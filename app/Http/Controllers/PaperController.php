<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperFormRequest;
use App\Http\Resources\PaperResource;
use App\Jobs\GeneratePaperQuestionsJob;
use App\Services\PaperService;
use App\Models\Paper;
use App\Models\Subject;
use App\Models\PaperCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class PaperController extends Controller
{
    protected $paperService;

    public function __construct(PaperService $paperService)
    {
        $this->paperService = $paperService;
        
        // Use gates defined in AuthServiceProvider instead of explicit permission middleware
        $this->authorizeResource(Paper::class, 'paper');
        $this->middleware('can:publish,paper')->only(['publish', 'archive']);
    }

    /**
     * Display a listing of papers with advanced filtering and sorting.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:draft,published,archived',
            'paper_category_id' => 'nullable|exists:paper_categories,id',
            'search' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:title,created_at,total_marks,passing_percentage',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);
        
        // Create a cache key based on filters
        $cacheKey = 'papers:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        // Cache results for better performance
        $papers = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($filters) {
            return $this->paperService->getPaginatedPapers($filters);
        });
        
        $paperCategories = Cache::remember('paper_categories:all', now()->addHours(6), function() {
            return PaperCategory::all();
        });
        
        // Get statistics for dashboard
        $stats = Cache::remember('papers:stats', now()->addHours(1), function() {
            return [
                'total' => Paper::count(),
                'published' => Paper::where('status', 'published')->count(),
                'draft' => Paper::where('status', 'draft')->count(),
                'archived' => Paper::where('status', 'archived')->count(),
                'avg_questions' => Paper::has('questions')->avg('questions_count') ?? 0,
            ];
        });
        
        if ($request->expectsJson()) {
            return PaperResource::collection($papers)
                ->additional(['stats' => $stats]);
        }
        
        return view('papers.index', compact('papers', 'paperCategories', 'filters', 'stats'));
    }

    /**
     * Show the form for creating a new paper with preloaded subjects.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get subjects and paper categories - cacheable data
        $subjects = Cache::remember('subjects:all', now()->addHours(6), function() {
            return Subject::orderBy('name')->get(['id', 'name', 'code']);
        });
        
        $paperCategories = Cache::remember('paper_categories:all', now()->addHours(6), function() {
            return PaperCategory::orderBy('name')->get(['id', 'name']);
        });
        
        // Get recommended settings
        $recommendedSettings = $this->paperService->getRecommendedPaperSettings();
        
        return view('papers.create', compact('subjects', 'paperCategories', 'recommendedSettings'));
    }

    /**
     * Store a newly created paper in storage and handle subject assignment.
     *
     * @param  PaperFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(PaperFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            // Begin a database transaction
            $paper = $this->paperService->createPaper($validatedData);
            
            // Clear the papers cache
            $this->clearPaperCaches();
            
            Event::dispatch('paper.created', $paper);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper created successfully',
                    'paper' => new PaperResource($paper),
                    'redirect' => route('papers.show', $paper->id)
                ], 201);
            }
            
            if ($request->has('add_subjects') && $request->add_subjects) {
                return redirect()->route('paper-subjects.create', ['paper' => $paper->id])
                    ->with('success', 'Paper created successfully! Now add subjects.');
            }
            
            return redirect()->route('papers.index')
                ->with('success', 'Paper created successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to create paper', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating paper: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified paper with detailed relationships.
     *
     * @param  Paper  $paper
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Paper $paper, Request $request)
    {
        // Cache individual paper data with relationships
        $cacheKey = "paper:{$paper->id}:details";
        
        $paper = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($paper) {
            // Eager load relationships to avoid N+1 query problem
            return $paper->load([
                'paperCategory', 
                'paperSubjects.subject',
                'questions' => function($query) {
                    $query->orderBy('order_index')->with([
                        'question:id,text,difficulty_level,marks',
                        'question.options:id,question_id,text,is_correct'
                    ]);
                }
            ]);
        });
        
        // Get paper statistics
        $statistics = Cache::remember("paper:{$paper->id}:stats", now()->addMinutes(15), function() use ($paper) {
            return $this->paperService->getPaperStatistics($paper);
        });
        
        if ($request->expectsJson()) {
            return (new PaperResource($paper))
                ->additional(['statistics' => $statistics]);
        }
        
        return view('papers.show', compact('paper', 'statistics'));
    }

    /**
     * Show the form for editing the specified paper.
     *
     * @param  Paper  $paper
     * @return \Illuminate\View\View
     */
    public function edit(Paper $paper)
    {
        $subjects = Cache::remember('subjects:all', now()->addHours(6), function() {
            return Subject::all(['id', 'name', 'code']);
        });
        
        $paperCategories = Cache::remember('paper_categories:all', now()->addHours(6), function() {
            return PaperCategory::all(['id', 'name']);
        });
        
        return view('papers.edit', compact('paper', 'subjects', 'paperCategories'));
    }

    /**
     * Update the specified paper in storage.
     *
     * @param  PaperFormRequest  $request
     * @param  Paper  $paper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(PaperFormRequest $request, Paper $paper)
    {
        $validatedData = $request->validated();
        
        try {
            $this->paperService->updatePaper($paper, $validatedData);
            
            // Clear relevant caches
            $this->clearPaperCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper updated successfully!',
                    'data' => new PaperResource($paper->fresh())
                ]);
            }
            
            return redirect()->route('papers.index')
                ->with('success', 'Paper updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update paper', [
                'id' => $paper->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating paper: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified paper from storage.
     *
     * @param  Paper  $paper
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Paper $paper, Request $request)
    {
        try {
            // Check if paper has test attempts
            if ($paper->testAttempts()->exists()) {
                throw new \Exception('Cannot delete paper that has test attempts');
            }
            
            $this->paperService->deletePaper($paper);
            
            // Clear relevant caches
            $this->clearPaperCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper deleted successfully!'
                ]);
            }
            
            return redirect()->route('papers.index')
                ->with('success', 'Paper deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete paper', [
                'id' => $paper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting paper: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of papers matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->validate(['search' => 'required|string|max:100'])['search'];
        
        $papers = $this->paperService->searchPapers($searchTerm);
        
        $paperCategories = Cache::remember('paper_categories:all', now()->addHours(6), function() {
            return PaperCategory::all(['id', 'name']);
        });
        
        if ($request->expectsJson()) {
            return PaperResource::collection($papers);
        }
        
        return view('papers.index', compact('papers', 'searchTerm', 'paperCategories'));
    }
    
    /**
     * Publish a paper and generate notification.
     *
     * @param  Paper  $paper
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function publish(Paper $paper, Request $request)
    {
        try {
            // Validate that paper has questions
            if ($paper->questions()->count() === 0) {
                throw new \Exception('Cannot publish a paper without questions');
            }
            
            $this->paperService->publishPaper($paper);
            
            // Clear relevant caches
            $this->clearPaperCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper published successfully',
                    'status' => $paper->status
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('success', 'Paper published successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to publish paper', [
                'id' => $paper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error publishing paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error publishing paper: ' . $e->getMessage());
        }
    }
    
    /**
     * Archive a paper.
     *
     * @param  Paper  $paper
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function archive(Paper $paper, Request $request)
    {
        try {
            $this->paperService->archivePaper($paper);
            
            // Clear relevant caches
            $this->clearPaperCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper archived successfully',
                    'status' => $paper->status
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('success', 'Paper archived successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to archive paper', [
                'id' => $paper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error archiving paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error archiving paper: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate questions for a paper from its subject distribution.
     *
     * @param  Paper  $paper
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function generateQuestions(Paper $paper, Request $request)
    {
        $this->authorize('update', $paper);
        
        try {
            // Validate paper can have questions generated
            if ($paper->status !== 'draft') {
                throw new \Exception('Can only generate questions for papers in draft status');
            }
            
            if ($paper->paperSubjects()->count() === 0) {
                throw new \Exception('Paper must have subjects assigned before generating questions');
            }
            
            // Use a job for better performance on large papers
            GeneratePaperQuestionsJob::dispatch($paper);
            
            // Clear relevant caches
            $this->clearPaperCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Questions are being generated. Please check back in a few moments.'
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('info', 'Questions are being generated. Please check back in a few moments.');
        } catch (\Exception $e) {
            Log::error('Failed to generate questions for paper', [
                'paper_id' => $paper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating questions: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error generating questions: ' . $e->getMessage());
        }
    }
    
    /**
     * Export paper with questions and options.
     *
     * @param Paper $paper
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Paper $paper, Request $request)
    {
        $format = $request->validate(['format' => 'required|in:pdf,docx,xlsx'])['format'];
        
        try {
            $export = $this->paperService->exportPaper($paper, $format);
            
            // Log the export
            Log::info('Paper exported', [
                'paper_id' => $paper->id,
                'format' => $format,
                'user_id' => auth()->id()
            ]);
            
            return $export;
        } catch (\Exception $e) {
            Log::error('Failed to export paper', [
                'paper_id' => $paper->id,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error exporting paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error exporting paper: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear paper-related caches.
     *
     * @param int|null $paperId
     * @return void
     */
    protected function clearPaperCaches($paperId = null)
    {
        Cache::forget('papers:list');
        Cache::forget('papers:stats');
        Cache::forget('papers:dropdown');
        Cache::forget('papers:for_test');
        
        if ($paperId) {
            Cache::forget("paper:{$paperId}:details");
            Cache::forget("paper:{$paperId}:stats");
            Cache::forget("paper:{$paperId}:questions");
            Cache::forget("paper:{$paperId}:subjects");
        }
    }
}