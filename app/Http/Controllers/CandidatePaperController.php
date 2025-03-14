<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidatePaperFormRequest;
use App\Http\Resources\CandidatePaperResource;
use App\Services\CandidatePaperService;
use App\Models\CandidatePaper;
use App\Models\TestAttempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CandidatePaperController extends Controller
{
    protected $candidatePaperService;

    public function __construct(CandidatePaperService $candidatePaperService)
    {
        $this->candidatePaperService = $candidatePaperService;
        
        // Use policy-based authorization instead of middleware
        $this->authorizeResource(CandidatePaper::class, 'candidatePaper');
    }

    /**
     * Display a listing of candidate papers with caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'test_attempt_id' => 'nullable|integer|exists:test_attempts,id',
            'question_id' => 'nullable|integer|exists:questions,id',
            'status' => 'nullable|in:answered,unanswered,pending_review',
            'sort_by' => 'nullable|in:created_at,updated_at,marks_obtained',
            'sort_dir' => 'nullable|in:asc,desc'
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'candidate_papers:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $candidatePapers = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($filters) {
            return $this->candidatePaperService->getPaginatedCandidatePapers($filters);
        });
        
        if ($request->expectsJson()) {
            return CandidatePaperResource::collection($candidatePapers);
        }
        
        return view('candidate_papers.index', compact('candidatePapers', 'filters'));
    }

    /**
     * Show the form for creating a new candidate paper.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Eager load related models to prevent N+1 query issues
        $testAttempts = Cache::remember('test_attempts:for_select', now()->addHours(1), function() {
            return TestAttempt::with('candidate', 'paper')->get();
        });
        
        // Filter to only get questions that aren't assigned yet
        $questions = Question::all();
        
        return view('candidate_papers.create', compact('testAttempts', 'questions'));
    }

    /**
     * Store a newly created candidate paper in storage.
     *
     * @param  CandidatePaperFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(CandidatePaperFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $candidatePaper = $this->candidatePaperService->createCandidatePaper($validatedData);
            
            // Clear relevant caches
            $this->clearCandidatePaperCaches($candidatePaper->test_attempt_id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate Paper created successfully!',
                    'data' => new CandidatePaperResource($candidatePaper)
                ], 201);
            }
            
            return redirect()->route('candidate-papers.index')
                ->with('success', 'Candidate Paper created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create candidate paper', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating candidate paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating candidate paper: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified candidate paper with relationships.
     *
     * @param  CandidatePaper  $candidatePaper
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(CandidatePaper $candidatePaper, Request $request)
    {
        // Cache individual candidate paper with relationships
        $cacheKey = "candidate_paper:{$candidatePaper->id}";
        
        $candidatePaper = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($candidatePaper) {
            return $candidatePaper->load([
                'testAttempt.candidate', 
                'testAttempt.paper', 
                'question.options'
            ]);
        });
        
        if ($request->expectsJson()) {
            return new CandidatePaperResource($candidatePaper);
        }
        
        return view('candidate_papers.show', compact('candidatePaper'));
    }

    /**
     * Show the form for editing the specified candidate paper.
     *
     * @param  CandidatePaper  $candidatePaper
     * @return \Illuminate\View\View
     */
    public function edit(CandidatePaper $candidatePaper)
    {
        $testAttempts = TestAttempt::with('candidate', 'paper')->get();
        $questions = Question::all();
        return view('candidate_papers.edit', compact('candidatePaper', 'testAttempts', 'questions'));
    }

    /**
     * Update the specified candidate paper in storage.
     *
     * @param  CandidatePaperFormRequest  $request
     * @param  CandidatePaper  $candidatePaper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(CandidatePaperFormRequest $request, CandidatePaper $candidatePaper)
    {
        $validatedData = $request->validated();
        
        try {
            $this->candidatePaperService->updateCandidatePaper($candidatePaper, $validatedData);
            
            // Clear relevant caches
            $this->clearCandidatePaperCaches($candidatePaper->test_attempt_id);
            Cache::forget("candidate_paper:{$candidatePaper->id}");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate Paper updated successfully!',
                    'data' => new CandidatePaperResource($candidatePaper->fresh())
                ]);
            }
            
            return redirect()->route('candidate-papers.index')
                ->with('success', 'Candidate Paper updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update candidate paper', [
                'id' => $candidatePaper->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating candidate paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating candidate paper: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified candidate paper from storage.
     *
     * @param  CandidatePaper  $candidatePaper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(CandidatePaper $candidatePaper, Request $request)
    {
        $testAttemptId = $candidatePaper->test_attempt_id;
        
        try {
            $this->candidatePaperService->deleteCandidatePaper($candidatePaper);
            
            // Clear relevant caches
            $this->clearCandidatePaperCaches($testAttemptId);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Candidate Paper deleted successfully!'
                ]);
            }
            
            return redirect()->route('candidate-papers.index')
                ->with('success', 'Candidate Paper deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete candidate paper', [
                'id' => $candidatePaper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting candidate paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting candidate paper: ' . $e->getMessage());
        }
    }

    /**
     * Display submissions for a specific test attempt.
     * 
     * @param TestAttempt $testAttempt
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function attemptSubmissions(TestAttempt $testAttempt, Request $request)
    {
        // Cache the submissions for this test attempt
        $cacheKey = "test_attempt:{$testAttempt->id}:submissions";
        
        $candidatePapers = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($testAttempt) {
            return $this->candidatePaperService->getSubmissionsForAttempt($testAttempt);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => CandidatePaperResource::collection($candidatePapers),
                'test_attempt' => $testAttempt->load('candidate', 'paper')
            ]);
        }
        
        return view('candidate_papers.submissions', compact('candidatePapers', 'testAttempt'));
    }
    
    /**
     * Submit a student answer for a question.
     *
     * @param Request $request
     * @param TestAttempt $testAttempt
     * @param Question $question
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request, TestAttempt $testAttempt, Question $question)
    {
        // First, validate that this is an active test attempt
        if ($testAttempt->status !== 'in_progress') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot submit answer: test not in progress'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Cannot submit answer: test not in progress');
        }
        
        $data = $request->validate([
            'selected_option_id' => 'nullable|exists:question_options,id',
            'answer_text' => 'nullable|string|max:5000',
        ]);
        
        try {
            $candidatePaper = $this->candidatePaperService->submitAnswer(
                $testAttempt,
                $question,
                $data['selected_option_id'] ?? null,
                $data['answer_text'] ?? null
            );
            
            // Clear relevant cache
            $this->clearCandidatePaperCaches($testAttempt->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Answer submitted successfully',
                    'data' => new CandidatePaperResource($candidatePaper)
                ]);
            }
            
            return redirect()->route('candidate.test', $testAttempt)
                ->with('success', 'Answer submitted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to submit answer', [
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error submitting answer: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error submitting answer: ' . $e->getMessage());
        }
    }
    
    /**
     * Grade a candidate's submission.
     *
     * @param Request $request
     * @param CandidatePaper $candidatePaper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function grade(Request $request, CandidatePaper $candidatePaper)
    {
        $this->authorize('grade', $candidatePaper);
        
        $data = $request->validate([
            'marks_obtained' => 'required|numeric|min:0|max:' . $candidatePaper->question->marks,
            'feedback' => 'nullable|string|max:1000',
            'is_correct' => 'required|boolean'
        ]);
        
        try {
            $graded = $this->candidatePaperService->gradePaper($candidatePaper, $data);
            
            // Clear relevant cache
            $this->clearCandidatePaperCaches($candidatePaper->test_attempt_id);
            Cache::forget("candidate_paper:{$candidatePaper->id}");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Submission graded successfully',
                    'data' => new CandidatePaperResource($graded)
                ]);
            }
            
            return redirect()->route('candidate-papers.attemptSubmissions', $candidatePaper->test_attempt_id)
                ->with('success', 'Submission graded successfully');
        } catch (\Exception $e) {
            Log::error('Failed to grade submission', [
                'id' => $candidatePaper->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error grading submission: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error grading submission: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear candidate paper related caches.
     *
     * @param int $testAttemptId
     * @return void
     */
    protected function clearCandidatePaperCaches($testAttemptId = null)
    {
        Cache::forget('candidate_papers:list');
        
        if ($testAttemptId) {
            Cache::forget("test_attempt:{$testAttemptId}:submissions");
            Cache::forget("test_attempt:{$testAttemptId}:details");
            Cache::forget("test_attempt:{$testAttemptId}:stats");
        }
    }
}
