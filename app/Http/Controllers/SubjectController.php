<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectFormRequest;
use App\Http\Resources\SubjectResource;
use App\Services\SubjectService;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubjectController extends Controller
{
    protected $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
        $this->middleware('permission:view-subjects')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-subjects')->only(['create', 'store', 'import']);
        $this->middleware('permission:edit-subjects')->only(['edit', 'update']);
        $this->middleware('permission:delete-subjects')->only('destroy');
    }

    /**
     * Display a listing of subjects with filtering and caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|in:active,inactive',
            'sort_by' => 'nullable|in:name,code,created_at',
            'sort_dir' => 'nullable|in:asc,desc'
        ]);
        
        // Create cache key based on filters
        $cacheKey = 'subjects:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        // Cache subjects list for better performance
        $subjects = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($filters) {
            return $this->subjectService->getPaginatedSubjects($filters);
        });
        
        if ($request->expectsJson()) {
            return SubjectResource::collection($subjects);
        }
        
        // Get statistics for dashboard
        $statistics = Cache::remember('subjects:stats', now()->addHours(1), function() {
            return [
                'total' => Subject::count(),
                'with_questions' => Subject::whereHas('questions')->count(),
                'avg_questions' => Subject::withCount('questions')->avg('questions_count') ?? 0,
            ];
        });
        
        return view('subjects.index', compact('subjects', 'filters', 'statistics'));
    }

    /**
     * Show the form for creating a new subject.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(SubjectFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $subject = $this->subjectService->createSubject($validatedData);
            
            // Clear relevant caches
            $this->clearSubjectCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject created successfully!',
                    'data' => new SubjectResource($subject)
                ], 201);
            }
            
            return redirect()->route('subjects.index')
                ->with('success', 'Subject created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create subject', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating subject: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified subject with detailed relationships.
     *
     * @param  Subject  $subject
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Subject $subject, Request $request)
    {
        // Cache individual subject with relationships
        $cacheKey = "subject:{$subject->id}:details";
        
        $subject = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($subject) {
            return $subject->load([
                'departments', 
                'facultyMembers.department', 
                'questions' => function($query) {
                    $query->latest()->take(10);
                }
            ]);
        });
        
        // Get subject statistics
        $stats = Cache::remember("subject:{$subject->id}:stats", now()->addMinutes(30), function() use ($subject) {
            return [
                'total_questions' => $subject->questions()->count(),
                'total_departments' => $subject->departments()->count(),
                'total_faculty' => $subject->facultyMembers()->count(),
                'difficulty_distribution' => [
                    'easy' => $subject->questions()->where('difficulty_level', 'easy')->count(),
                    'medium' => $subject->questions()->where('difficulty_level', 'medium')->count(),
                    'hard' => $subject->questions()->where('difficulty_level', 'hard')->count(),
                ]
            ];
        });
        
        if ($request->expectsJson()) {
            return (new SubjectResource($subject))->additional(['stats' => $stats]);
        }
        
        return view('subjects.show', compact('subject', 'stats'));
    }

    /**
     * Show the form for editing the specified subject.
     *
     * @param  Subject  $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @param  Subject  $subject
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(SubjectFormRequest $request, Subject $subject)
    {
        $validatedData = $request->validated();
        
        try {
            $this->subjectService->updateSubject($subject, $validatedData);
            
            // Clear relevant caches
            $this->clearSubjectCaches($subject->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject updated successfully!',
                    'data' => new SubjectResource($subject->fresh())
                ]);
            }
            
            return redirect()->route('subjects.index')
                ->with('success', 'Subject updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update subject', [
                'id' => $subject->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating subject: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  Subject  $subject
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Subject $subject, Request $request)
    {
        try {
            // Check if subject has related questions or papers
            $relatedQuestions = $subject->questions()->exists();
            $relatedPapers = $subject->papers()->exists();
            
            if ($relatedQuestions || $relatedPapers) {
                throw new \Exception(
                    'Cannot delete subject with related ' .
                    ($relatedQuestions ? 'questions ' : '') .
                    ($relatedPapers ? 'papers' : '')
                );
            }
            
            $this->subjectService->deleteSubject($subject);
            
            // Clear relevant caches
            $this->clearSubjectCaches($subject->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject deleted successfully!'
                ]);
            }
            
            return redirect()->route('subjects.index')
                ->with('success', 'Subject deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete subject', [
                'id' => $subject->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting subject: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of subjects matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->validate([
            'search' => 'required|string|min:2|max:100'
        ])['search'];
        
        // Cache search results for common search terms
        $cacheKey = 'subjects:search:' . md5($searchTerm);
        
        $subjects = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($searchTerm) {
            return $this->subjectService->searchSubjects($searchTerm);
        });
        
        if ($request->expectsJson()) {
            return SubjectResource::collection($subjects);
        }
        
        return view('subjects.index', compact('subjects', 'searchTerm'));
    }
    
    /**
     * Import subjects from CSV/Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:2048'
        ]);
        
        try {
            $file = $request->file('file');
            $count = $this->subjectService->importSubjects($file);
            
            // Clear all subject caches
            $this->clearSubjectCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subjects imported successfully!"
                ]);
            }
            
            return redirect()->route('subjects.index')
                ->with('success', "{$count} subjects imported successfully!");
        } catch (\Exception $e) {
            Log::error('Failed to import subjects', [
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error importing subjects: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error importing subjects: ' . $e->getMessage());
        }
    }
    
    /**
     * Export subjects to CSV/Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $format = $request->validate(['format' => 'required|in:csv,xlsx'])['format'];
        
        try {
            return $this->subjectService->exportSubjects($format);
        } catch (\Exception $e) {
            Log::error('Failed to export subjects', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error exporting subjects: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error exporting subjects: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear subject-related caches.
     *
     * @param int|null $subjectId
     * @return void
     */
    protected function clearSubjectCaches($subjectId = null)
    {
        Cache::forget('subjects:list');
        Cache::forget('subjects:stats');
        Cache::forget('subjects:dropdown');
        Cache::forget('subjects:all');
        
        if ($subjectId) {
            Cache::forget("subject:{$subjectId}:details");
            Cache::forget("subject:{$subjectId}:stats");
            Cache::forget("subject:{$subjectId}:departments");
            Cache::forget("subject:{$subjectId}:faculty");
        }
    }
}