<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestAttemptFormRequest;
use App\Http\Resources\TestAttemptResource;
use App\Services\TestAttemptService;
use App\Models\TestAttempt;
use App\Models\Candidate;
use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TestAttemptController extends Controller
{
    protected $testAttemptService;

    public function __construct(TestAttemptService $testAttemptService)
    {
        $this->testAttemptService = $testAttemptService;
        $this->middleware('permission:view-test-attempts')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-test-attempts')->only(['create', 'store']);
        $this->middleware('permission:edit-test-attempts')->only(['edit', 'update']);
        $this->middleware('permission:delete-test-attempts')->only('destroy');
        $this->middleware('permission:manage-test-attempts')->only(['startTest', 'endTest', 'extendTime', 'regrade']);
    }

    /**
     * Display a listing of test attempts with filtering and caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'candidate_id' => 'nullable|exists:candidates,id',
            'paper_id' => 'nullable|exists:papers,id',
            'status' => 'nullable|in:pending,in_progress,completed,expired',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'test_attempts:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $testAttempts = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($filters) {
            return $this->testAttemptService->getPaginatedTestAttempts($filters);
        });
        
        // Get stats from cache or compute
        $stats = Cache::remember('test_attempts:stats', now()->addHours(1), function() {
            return [
                'total' => TestAttempt::count(),
                'completed' => TestAttempt::where('status', 'completed')->count(),
                'in_progress' => TestAttempt::where('status', 'in_progress')->count(),
                'pending' => TestAttempt::where('status', 'pending')->count(),
                'expired' => TestAttempt::where('status', 'expired')->count(),
                'pass_rate' => TestAttempt::where('status', 'completed')
                    ->whereNotNull('score')
                    ->whereNotNull('passing_score')
                    ->whereRaw('score >= passing_score')
                    ->count() / max(1, TestAttempt::where('status', 'completed')->count()) * 100
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'data' => TestAttemptResource::collection($testAttempts),
                'stats' => $stats
            ]);
        }
        
        $candidates = Cache::remember('candidates:dropdown', now()->addHours(2), function() {
            return Candidate::select('id', 'name')->get();
        });
        
        $papers = Cache::remember('papers:dropdown', now()->addHours(2), function() {
            return Paper::select('id', 'title', 'paper_code')->get();
        });
        
        return view('test_attempts.index', compact('testAttempts', 'filters', 'stats', 'candidates', 'papers'));
    }

    /**
     * Show the form for creating a new test attempt.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // If candidate_id is provided, preselect that candidate
        $selectedCandidateId = $request->input('candidate_id');
        
        $candidates = Cache::remember('candidates:for_select', now()->addHours(1), function() {
            return Candidate::where('status', 'active')->get(['id', 'name', 'email']);
        });
        
        $papers = Cache::remember('papers:for_test', now()->addHours(1), function() {
            return Paper::where('status', 'published')->get(['id', 'title', 'paper_code', 'total_marks', 'passing_percentage']);
        });
        
        return view('test_attempts.create', compact('candidates', 'papers', 'selectedCandidateId'));
    }

    /**
     * Store a newly created test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(TestAttemptFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $testAttempt = $this->testAttemptService->createTestAttempt($validatedData);
            
            // Clear relevant caches
            $this->clearTestAttemptCaches();
            Cache::forget("candidate:{$testAttempt->candidate_id}:test_attempts");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test Attempt created successfully!',
                    'data' => new TestAttemptResource($testAttempt)
                ], 201);
            }
            
            if ($request->has('start_test') && $request->start_test) {
                return redirect()->route('test-attempts.startTest', $testAttempt->id)
                    ->with('success', 'Test assigned successfully! Starting test now.');
            }
            
            return redirect()->route('test-attempts.index')
                ->with('success', 'Test Attempt created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create test attempt', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating test attempt: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating test attempt: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified test attempt with detailed relationships.
     *
     * @param  TestAttempt  $testAttempt
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(TestAttempt $testAttempt, Request $request)
    {
        // Cache key for this specific test attempt with details
        $cacheKey = "test_attempt:{$testAttempt->id}:details";
        
        $testAttempt = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($testAttempt) {
            // Eager load relationships to prevent N+1 query problem
            return $testAttempt->load([
                'candidate', 
                'paper.paperCategory',
                'candidatePapers' => function($query) {
                    $query->with([
                        'question.question:id,text,marks,question_type_id',
                        'question.question.options:id,question_id,text,is_correct'
                    ]);
                }
            ]);
        });
        
        // Get attempt statistics
        $stats = Cache::remember("test_attempt:{$testAttempt->id}:stats", now()->addMinutes(10), function() use ($testAttempt) {
            return $this->testAttemptService->getAttemptStatistics($testAttempt);
        });
        
        if ($request->expectsJson()) {
            return (new TestAttemptResource($testAttempt))
                ->additional(['stats' => $stats]);
        }
        
        return view('test_attempts.show', compact('testAttempt', 'stats'));
    }

    /**
     * Show the form for editing the specified test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\View\View
     */
    public function edit(TestAttempt $testAttempt)
    {
        $candidates = Candidate::where('status', 'active')->get(['id', 'name', 'email']);
        $papers = Paper::where('status', 'published')->get(['id', 'title', 'paper_code', 'total_marks', 'passing_percentage']);
        return view('test_attempts.edit', compact('testAttempt', 'candidates', 'papers'));
    }

    /**
     * Update the specified test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(TestAttemptFormRequest $request, TestAttempt $testAttempt)
    {
        $validatedData = $request->validated();
        
        try {
            // Check if we're allowed to update this attempt based on status
            if (in_array($testAttempt->status, ['completed', 'expired'])) {
                throw new \Exception('Cannot update a test attempt that has been completed or expired');
            }
            
            $this->testAttemptService->updateTestAttempt($testAttempt, $validatedData);
            
            // Clear relevant caches
            $this->clearTestAttemptCaches($testAttempt->id);
            Cache::forget("candidate:{$testAttempt->candidate_id}:test_attempts");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test Attempt updated successfully!',
                    'data' => new TestAttemptResource($testAttempt->fresh())
                ]);
            }
            
            return redirect()->route('test-attempts.index')
                ->with('success', 'Test Attempt updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update test attempt', [
                'id' => $testAttempt->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating test attempt: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating test attempt: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified test attempt from storage.
     *
     * @param  TestAttempt  $testAttempt
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(TestAttempt $testAttempt, Request $request)
    {
        try {
            // Store candidate_id before deletion for cache clearing
            $candidateId = $testAttempt->candidate_id;
            
            $this->testAttemptService->deleteTestAttempt($testAttempt);
            
            // Clear caches
            $this->clearTestAttemptCaches($testAttempt->id);
            Cache::forget("candidate:{$candidateId}:test_attempts");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test Attempt deleted successfully!'
                ]);
            }
            
            return redirect()->route('test-attempts.index')
                ->with('success', 'Test Attempt deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete test attempt', [
                'id' => $testAttempt->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting test attempt: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting test attempt: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of test attempts matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->validate(['search' => 'required|string|max:100'])['search'];
        
        $testAttempts = $this->testAttemptService->searchTestAttempts($searchTerm);
        
        if ($request->expectsJson()) {
            return TestAttemptResource::collection($testAttempts);
        }
        
        $candidates = Candidate::select('id', 'name')->get();
        $papers = Paper::select('id', 'title', 'paper_code')->get();
        
        return view('test_attempts.index', compact('testAttempts', 'searchTerm', 'candidates', 'papers'));
    }
    
    /**
     * Start a test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function startTest(TestAttempt $testAttempt, Request $request)
    {
        try {
            $this->testAttemptService->startTestAttempt($testAttempt);
            
            // Clear caches
            $this->clearTestAttemptCaches($testAttempt->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test started successfully!',
                    'data' => new TestAttemptResource($testAttempt->fresh())
                ]);
            }
            
            return redirect()->route('candidate.test', $testAttempt)
                ->with('success', 'Test started successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to start test attempt', [
                'id' => $testAttempt->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error starting test: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error starting test: ' . $e->getMessage());
        }
    }
    
    /**
     * End a test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function endTest(TestAttempt $testAttempt, Request $request)
    {
        try {
            $this->testAttemptService->endTestAttempt($testAttempt);
            
            // Clear caches
            $this->clearTestAttemptCaches($testAttempt->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test completed successfully!',
                    'data' => new TestAttemptResource($testAttempt->fresh())
                ]);
            }
            
            return redirect()->route('candidate.results', $testAttempt)
                ->with('success', 'Test completed successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to end test attempt', [
                'id' => $testAttempt->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error ending test: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error ending test: ' . $e->getMessage());
        }
    }
    
    /**
     * Extend the time for a test attempt.
     *
     * @param  Request  $request
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function extendTime(Request $request, TestAttempt $testAttempt)
    {
        $validatedData = $request->validate([
            'additional_minutes' => 'required|integer|min:1|max:120'
        ]);
        
        try {
            $this->testAttemptService->extendTestTime($testAttempt, $validatedData['additional_minutes']);
            
            // Clear caches
            $this->clearTestAttemptCaches($testAttempt->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test time extended successfully!',
                    'data' => new TestAttemptResource($testAttempt->fresh())
                ]);
            }
            
            return redirect()->route('test-attempts.show', $testAttempt)
                ->with('success', 'Test time extended successfully by ' . $validatedData['additional_minutes'] . ' minutes!');
        } catch (\Exception $e) {
            Log::error('Failed to extend test time', [
                'id' => $testAttempt->id,
                'minutes' => $validatedData['additional_minutes'],
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error extending test time: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error extending test time: ' . $e->getMessage());
        }
    }
    
    /**
     * Regrade a test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function regrade(TestAttempt $testAttempt, Request $request)
    {
        try {
            $this->testAttemptService->regradeTestAttempt($testAttempt);
            
            // Clear caches
            $this->clearTestAttemptCaches($testAttempt->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test attempt regraded successfully!',
                    'data' => new TestAttemptResource($testAttempt->fresh())
                ]);
            }
            
            return redirect()->route('test-attempts.show', $testAttempt)
                ->with('success', 'Test attempt regraded successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to regrade test attempt', [
                'id' => $testAttempt->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error regrading test attempt: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error regrading test attempt: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear test attempt related caches.
     *
     * @param int|null $testAttemptId
     * @return void
     */
    protected function clearTestAttemptCaches($testAttemptId = null)
    {
        Cache::forget('test_attempts:list');
        Cache::forget('test_attempts:stats');
        
        if ($testAttemptId) {
            Cache::forget("test_attempt:{$testAttemptId}:details");
            Cache::forget("test_attempt:{$testAttemptId}:stats");
        }
    }
}