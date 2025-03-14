<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
        $this->middleware('permission:view-audit-logs')->only(['index', 'show', 'filter', 'export']);
        $this->middleware('permission:delete-audit-logs')->only('destroy');
        $this->middleware('permission:clear-audit-logs')->only('clearOldLogs');
    }

    /**
     * Display a listing of audit logs with filtering, analytics, and caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'action' => 'nullable|string',
            'entity_affected' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:created_at,action,entity_affected,user_id',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'audit_logs:list:' . md5(json_encode($filters) . $request->input('page', 1));
        
        // Retrieve data with caching for better performance
        $auditLogs = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($filters) {
            return $this->auditLogService->getPaginatedAuditLogs($filters);
        });
        
        // Get dropdown values from cache or database
        $actions = Cache::remember('audit_logs:actions', now()->addHours(6), function() {
            return AuditLog::distinct('action')->pluck('action');
        });
        
        $entities = Cache::remember('audit_logs:entities', now()->addHours(6), function() {
            return AuditLog::distinct('entity_affected')->pluck('entity_affected');
        });
        
        $users = Cache::remember('audit_logs:users', now()->addHours(6), function() {
            return DB::table('audit_logs')
                ->join('users', 'users.id', '=', 'audit_logs.user_id')
                ->select('audit_logs.user_id', 'users.name as user_name')
                ->distinct('audit_logs.user_id')
                ->pluck('user_name', 'user_id')
                ->toArray();
        });
        
        // Get analytics data for visualization
        $activityByDay = Cache::remember('audit_logs:activity_by_day', now()->addHours(1), function() {
            return DB::table('audit_logs')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->pluck('count', 'date')
                ->toArray();
        });
        
        $activityByAction = Cache::remember('audit_logs:activity_by_action', now()->addHours(1), function() {
            return DB::table('audit_logs')
                ->select('action', DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'action')
                ->toArray();
        });
        
        // Get frequently modified entities
        $topEntities = Cache::remember('audit_logs:top_entities', now()->addHours(2), function() {
            return DB::table('audit_logs')
                ->select('entity_affected', DB::raw('COUNT(*) as count'))
                ->whereNotNull('entity_affected')
                ->groupBy('entity_affected')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->pluck('count', 'entity_affected')
                ->toArray();
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'data' => AuditLogResource::collection($auditLogs),
                'metadata' => [
                    'actions' => $actions,
                    'entities' => $entities,
                    'analytics' => [
                        'activity_by_day' => $activityByDay,
                        'activity_by_action' => $activityByAction,
                        'top_entities' => $topEntities
                    ]
                ]
            ]);
        }
        
        return view('audit_logs.index', compact(
            'auditLogs', 
            'filters', 
            'actions', 
            'entities', 
            'users',
            'activityByDay',
            'activityByAction',
            'topEntities'
        ));
    }

    /**
     * Display the specified audit log with related entities.
     *
     * @param  AuditLog  $auditLog
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(AuditLog $auditLog, Request $request)
    {
        $cacheKey = "audit_log:{$auditLog->id}";
        
        $data = Cache::remember($cacheKey, now()->addDay(), function() use ($auditLog) {
            // Eager load user
            $auditLog->load('user');
            
            // Try to load related entity based on entity_affected field
            $relatedEntity = null;
            $beforeState = null;
            $afterState = null;
            
            if ($auditLog->entity_affected) {
                try {
                    [$entityType, $entityId] = explode(':', $auditLog->entity_affected);
                    $modelClass = 'App\\Models\\' . ucfirst($entityType);
                    
                    if (class_exists($modelClass) && $entityId) {
                        $relatedEntity = $modelClass::find($entityId);
                    }
                    
                    // Parse details JSON for structured display
                    $details = json_decode($auditLog->details, true) ?? ['raw' => $auditLog->details];
                    
                    // Extract before/after states if available
                    if (isset($details['before']) && isset($details['after'])) {
                        $beforeState = $details['before'];
                        $afterState = $details['after'];
                    }
                } catch (\Exception $e) {
                    // Silently handle failures to load related entity
                    Log::info('Failed to load related entity for audit log', [
                        'audit_log_id' => $auditLog->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Parse details JSON for structured display
            $details = json_decode($auditLog->details, true) ?? ['raw' => $auditLog->details];
            
            return [
                'auditLog' => $auditLog,
                'relatedEntity' => $relatedEntity,
                'details' => $details,
                'beforeState' => $beforeState,
                'afterState' => $afterState
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => array_merge(
                    ['audit_log' => new AuditLogResource($data['auditLog'])],
                    $data
                )
            ]);
        }
        
        return view('audit_logs.show', $data);
    }

    /**
     * Remove the specified audit log from storage.
     *
     * @param  AuditLog  $auditLog
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(AuditLog $auditLog, Request $request)
    {
        try {
            $this->auditLogService->deleteAuditLog($auditLog);
            
            // Clear cache
            Cache::forget("audit_log:{$auditLog->id}");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Audit log deleted successfully!'
                ]);
            }
            
            return redirect()->route('audit-logs.index')
                ->with('success', 'Audit log deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete audit log', [
                'id' => $auditLog->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting audit log: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting audit log: ' . $e->getMessage());
        }
    }

    /**
     * Export audit logs to CSV/Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $filters = $request->validate([
            'action' => 'nullable|string',
            'entity_affected' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'format' => 'required|in:csv,excel'
        ]);
        
        try {
            $export = $this->auditLogService->exportAuditLogs($filters);
            
            // Log the export
            $this->auditLogService->logAction(
                'export', 
                'audit_logs', 
                null, 
                ['filters' => $filters]
            );
            
            return $export;
        } catch (\Exception $e) {
            Log::error('Failed to export audit logs', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error exporting audit logs: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error exporting audit logs: ' . $e->getMessage());
        }
    }

    /**
     * Filter logs by specific criteria.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        $filters = $request->validate([
            'action' => 'required|string',
            'entity_affected' => 'nullable|string',
            'date_range' => 'nullable|string', // A range like "last7days", "last30days", "thismonth"
        ]);
        
        $auditLogs = $this->auditLogService->getFilteredLogs($filters);
        
        if ($request->expectsJson()) {
            return AuditLogResource::collection($auditLogs);
        }
        
        // Direct to index view with filtered results
        return view('audit_logs.index', compact('auditLogs', 'filters'));
    }
    
    /**
     * Get statistics about audit logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function statistics(Request $request)
    {
        $cacheKey = 'audit_logs:statistics';
        
        $statistics = Cache::remember($cacheKey, now()->addHour(), function() {
            return $this->auditLogService->getAuditLogStatistics();
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        }
        
        return view('audit_logs.statistics', compact('statistics'));
    }
    
    /**
     * Clear old logs based on retention policy.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function clearOldLogs(Request $request)
    {
        $data = $request->validate([
            'days' => 'required|integer|min:7|max:365'
        ]);
        
        $days = $data['days'];
        
        try {
            $count = $this->auditLogService->clearLogsOlderThan($days);
            
            // Clear all audit log caches
            $this->clearAuditLogCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Cleared {$count} audit logs older than {$days} days.",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('audit-logs.index')
                ->with('success', "Cleared {$count} audit logs older than {$days} days.");
        } catch (\Exception $e) {
            Log::error('Failed to clear old audit logs', [
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing old logs: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->route('audit-logs.index')
                ->with('error', 'Error clearing old logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Get user activity history.
     *
     * @param int $userId
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function userActivity($userId, Request $request)
    {
        $cacheKey = "audit_logs:user:{$userId}:" . ($request->page ?? 1);
        
        $activityLogs = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($userId) {
            return $this->auditLogService->getUserActivityLogs($userId);
        });
        
        // Get analytics for this user
        $userStats = Cache::remember("audit_logs:user:{$userId}:stats", now()->addHours(1), function() use ($userId) {
            return $this->auditLogService->getUserActivityStatistics($userId);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => AuditLogResource::collection($activityLogs),
                'statistics' => $userStats
            ]);
        }
        
        return view('audit_logs.user_activity', compact('activityLogs', 'userStats', 'userId'));
    }
    
    /**
     * Get entity activity history.
     *
     * @param string $entityType
     * @param int $entityId
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function entityActivity($entityType, $entityId, Request $request)
    {
        $entityKey = "{$entityType}:{$entityId}";
        $cacheKey = "audit_logs:entity:{$entityKey}:" . ($request->page ?? 1);
        
        $activityLogs = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($entityKey) {
            return $this->auditLogService->getEntityActivityLogs($entityKey);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => AuditLogResource::collection($activityLogs)
            ]);
        }
        
        // Try to load the entity instance
        $entity = null;
        try {
            $modelClass = 'App\\Models\\' . ucfirst($entityType);
            if (class_exists($modelClass)) {
                $entity = $modelClass::find($entityId);
            }
        } catch (\Exception $e) {
            // Silently fail, $entity will remain null
        }
        
        return view('audit_logs.entity_activity', compact('activityLogs', 'entityType', 'entityId', 'entity'));
    }
    
    /**
     * Clear audit log related caches.
     *
     * @return void
     */
    protected function clearAuditLogCaches()
    {
        Cache::forget('audit_logs:list');
        Cache::forget('audit_logs:actions');
        Cache::forget('audit_logs:entities');
        Cache::forget('audit_logs:users');
        Cache::forget('audit_logs:activity_by_day');
        Cache::forget('audit_logs:activity_by_action');
        Cache::forget('audit_logs:top_entities');
        Cache::forget('audit_logs:statistics');
    }
}
