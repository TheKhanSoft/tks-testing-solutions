<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateFormRequest;
use App\Http\Resources\CandidateResource;
use App\Services\CandidateService;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CandidateController extends Controller
{
    protected $candidateService;

    public function __construct(CandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
        $this->authorizeResource(Candidate::class, 'candidate');
    }

    /**
     * Display a listing of candidates with filtering and caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Extract filters from request
        $filters = $request->validate([
            'status' => 'nullable|in:active,inactive,blocked',
            'search' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:name,email,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);
        
        $page = $request->input('page', 1);
        
        // Cache the candidates list with filters as part of the key
        $cacheKey = 'candidates:list:' . md5(json_encode($filters) . $page);
        
        $candidates = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($filters) {
            return $this->candidateService->getPaginatedCandidates($filters);
        });
        
        // Get statistics for dashboard
        $statistics = Cache::remember('candidates:stats', now()->addHours(1), function() {
            return [
                'total' => Candidate::count(),
                'active' => Candidate::where('status', 'active')->count(),
                'inactive' => Candidate::where('status', 'inactive')->count(),
                'blocked' => Candidate::where('status', 'blocked')->count(),
                'tests_taken' => Candidate::withCount('testAttempts')
                    ->get()
                    ->sum('test_attempts_count')
            ];
        });
        
        if ($request->expectsJson()) {
            return CandidateResource::collection($candidates)
                ->additional(['statistics' => $statistics]);
        }
        
        return view('candidates.index', compact('candidates', 'filters', 'statistics'));
    }

    /**
     * Show the form for creating a new candidate.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('candidates.create');
    }

    /**
     * Store a newly created candidate in storage.
     *
     * @param  CandidateFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(CandidateFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $candidate = $this->candidateService->createCandidate($validatedData);
            
            // Clear relevant caches
            $this->clearCandidateCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate created successfully!',
                    'data' => new CandidateResource($candidate)
                ], 201);
            }
            
            return redirect()->route('candidates.index')
                ->with('success', 'Candidate created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create candidate', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating candidate: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating candidate: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified candidate with relationships.
     *
     * @param  Candidate  $candidate
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Candidate $candidate, Request $request)
    {
        // Cache the candidate with its relationships
        $cacheKey = "candidate:{$candidate->id}:details";
        
        $candidate = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($candidate) {
            return $candidate->load([
                'testAttempts' => function($query) {
                    $query->latest()->with('paper');
                }
            ]);
        });
        
        // Get performance statistics
        $statistics = Cache::remember("candidate:{$candidate->id}:stats", now()->addHours(1), function() use ($candidate) {
            return [
                'tests_taken' => $candidate->testAttempts->count(),
                'tests_passed' => $candidate->testAttempts
                    ->where('status', 'completed')
                    ->filter(function($attempt) {
                        return $attempt->score >= $attempt->passing_score;
                    })->count(),
                'average_score' => $candidate->testAttempts
                    ->where('status', 'completed')
                    ->avg('score') ?? 0,
                'last_test_date' => $candidate->testAttempts->first()?->created_at ?? null
            ];
        });
        
        if ($request->expectsJson()) {
            return (new CandidateResource($candidate))
                ->additional(['statistics' => $statistics]);
        }
        
        return view('candidates.show', compact('candidate', 'statistics'));
    }

    /**
     * Show the form for editing the specified candidate.
     *
     * @param  Candidate  $candidate
     * @return \Illuminate\View\View
     */
    public function edit(Candidate $candidate)
    {
        return view('candidates.edit', compact('candidate'));
    }

    /**
     * Update the specified candidate in storage.
     *
     * @param  CandidateFormRequest  $request
     * @param  Candidate  $candidate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(CandidateFormRequest $request, Candidate $candidate)
    {
        $validatedData = $request->validated();
        
        try {
            $this->candidateService->updateCandidate($candidate, $validatedData);
            
            // Clear relevant caches
            $this->clearCandidateCaches($candidate->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate updated successfully!',
                    'data' => new CandidateResource($candidate->fresh())
                ]);
            }
            
            return redirect()->route('candidates.index')
                ->with('success', 'Candidate updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update candidate', [
                'id' => $candidate->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating candidate: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating candidate: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified candidate from storage with proper cleanup.
     *
     * @param  Candidate  $candidate
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Candidate $candidate, Request $request)
    {
        try {
            // Check if candidate has test attempts
            if ($candidate->testAttempts()->exists()) {
                throw new \Exception('Cannot delete candidate with test attempts');
            }
            
            $this->candidateService->deleteCandidate($candidate);
            
            // Clear relevant caches
            $this->clearCandidateCaches($candidate->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate deleted successfully!'
                ]);
            }
            
            return redirect()->route('candidates.index')
                ->with('success', 'Candidate deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete candidate', [
                'id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting candidate: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting candidate: ' . $e->getMessage());
        }
    }

    /**
     * Advanced search with multiple parameters
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $filters = $request->validate([
            'search' => 'required|string|max:100',
            'status' => 'nullable|in:active,inactive,blocked',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);
        
        $candidates = $this->candidateService->searchCandidates($filters);
        
        if ($request->expectsJson()) {
            return CandidateResource::collection($candidates);
        }
        
        return view('candidates.index', compact('candidates', 'filters'));
    }
    
    /**
     * Export candidates to CSV/Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $format = $request->validate(['format' => 'required|in:csv,xlsx'])['format'];
        
        try {
            return $this->candidateService->exportCandidates($format);
        } catch (\Exception $e) {
            Log::error('Failed to export candidates', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error exporting candidates: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error exporting candidates: ' . $e->getMessage());
        }
    }
    
    /**
     * Set candidate status (active/inactive/blocked).
     *
     * @param Request $request
     * @param Candidate $candidate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function setStatus(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'status' => 'required|in:active,inactive,blocked'
        ]);
        
        try {
            $this->candidateService->updateCandidateStatus($candidate, $data['status']);
            
            // Clear relevant caches
            $this->clearCandidateCaches($candidate->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Candidate status changed to {$data['status']} successfully!",
                    'data' => new CandidateResource($candidate->fresh())
                ]);
            }
            
            return redirect()->back()
                ->with('success', "Candidate status changed to {$data['status']} successfully!");
        } catch (\Exception $e) {
            Log::error('Failed to update candidate status', [
                'id' => $candidate->id,
                'status' => $data['status'],
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating candidate status: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating candidate status: ' . $e->getMessage());
        }
    }
    
    /**
     * Get test results for a candidate.
     *
     * @param Candidate $candidate
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function testResults(Candidate $candidate, Request $request)
    {
        $cacheKey = "candidate:{$candidate->id}:test_attempts:" . ($request->page ?? 1);
        
        $testAttempts = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($candidate) {
            return $candidate->testAttempts()
                ->with('paper')
                ->latest()
                ->paginate(10);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $testAttempts
            ]);
        }
        
        return view('candidates.test_results', compact('candidate', 'testAttempts'));
    }
    
    /**
     * Clear candidate related caches.
     *
     * @param int|null $candidateId
     * @return void
     */
    protected function clearCandidateCaches($candidateId = null)
    {
        Cache::forget('candidates:list');
        Cache::forget('candidates:stats');
        Cache::forget('candidates:dropdown');
        Cache::forget('candidates:for_select');
        
        if ($candidateId) {
            Cache::forget("candidate:{$candidateId}:details");
            Cache::forget("candidate:{$candidateId}:stats");
            
            // Clear test attempts pages
            for ($i = 1; $i <= 5; $i++) {
                Cache::forget("candidate:{$candidateId}:test_attempts:{$i}");
            }
        }
    }
}
