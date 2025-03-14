<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperQuestionFormRequest;
use App\Http\Resources\PaperQuestionResource;
use App\Services\PaperQuestionService;
use App\Models\PaperQuestion;
use App\Models\Paper;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaperQuestionController extends Controller
{
    protected $paperQuestionService;

    public function __construct(PaperQuestionService $paperQuestionService)
    {
        $this->paperQuestionService = $paperQuestionService;
        $this->middleware('permission:view-paper-questions')->only(['index', 'show', 'paperQuestions']);
        $this->middleware('permission:create-paper-questions')->only(['create', 'store', 'addQuestions', 'storeMultiple']);
        $this->middleware('permission:edit-paper-questions')->only(['edit', 'update', 'reorder']);
        $this->middleware('permission:delete-paper-questions')->only('destroy');
    }

    /**
     * Display a listing of paper questions.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'paper_id' => 'nullable|exists:papers,id',
            'question_id' => 'nullable|exists:questions,id',
        ]);
        
        $cacheKey = 'paper_questions:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $paperQuestions = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($filters) {
            return $this->paperQuestionService->getPaginatedPaperQuestions($filters);
        });
        
        if ($request->expectsJson()) {
            return PaperQuestionResource::collection($paperQuestions);
        }
        
        return view('paper_questions.index', compact('paperQuestions', 'filters'));
    }

    /**
     * Show the form for creating a new paper question.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $papers = Cache::remember('papers:dropdown', now()->addHours(3), function() {
            return Paper::all(['id', 'title', 'paper_code']);
        });
        
        $questions = Cache::remember('questions:dropdown', now()->addHours(1), function() {
            return Question::with('subject')->get(['id', 'text', 'subject_id', 'difficulty_level']);
        });
        
        return view('paper_questions.create', compact('papers', 'questions'));
    }

    /**
     * Store a newly created paper question in storage.
     *
     * @param  PaperQuestionFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(PaperQuestionFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $paperQuestion = $this->paperQuestionService->createPaperQuestion($validatedData);
            
            // Clear cache
            $this->clearPaperQuestionCaches($validatedData['paper_id']);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question added to paper successfully!',
                    'data' => new PaperQuestionResource($paperQuestion)
                ], 201);
            }
            
            if ($request->has('redirect_to_paper') && $request->redirect_to_paper) {
                return redirect()->route('papers.show', $validatedData['paper_id'])
                    ->with('success', 'Question added to paper successfully!');
            }
            
            return redirect()->route('paper-questions.index')
                ->with('success', 'Question added to paper successfully!');
        } catch (\Exception $e) {
            Log::error('Error adding question to paper', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding question to paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding question to paper: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified paper question with relationships.
     *
     * @param  PaperQuestion  $paperQuestion
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(PaperQuestion $paperQuestion, Request $request)
    {
        $cacheKey = "paper_question:{$paperQuestion->id}";
        
        $paperQuestion = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($paperQuestion) {
            return $paperQuestion->load([
                'paper',
                'question.subject', 
                'question.options'
            ]);
        });
        
        if ($request->expectsJson()) {
            return new PaperQuestionResource($paperQuestion);
        }
        
        return view('paper_questions.show', compact('paperQuestion'));
    }

    /**
     * Show the form for editing the specified paper question.
     *
     * @param  PaperQuestion  $paperQuestion
     * @return \Illuminate\View\View
     */
    public function edit(PaperQuestion $paperQuestion)
    {
        $papers = Paper::all();
        $questions = Question::with('subject')->get();
        return view('paper_questions.edit', compact('paperQuestion', 'papers', 'questions'));
    }

    /**
     * Update the specified paper question in storage.
     *
     * @param  PaperQuestionFormRequest  $request
     * @param  PaperQuestion  $paperQuestion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PaperQuestionFormRequest $request, PaperQuestion $paperQuestion)
    {
        $validatedData = $request->validated();
        
        try {
            $this->paperQuestionService->updatePaperQuestion($paperQuestion, $validatedData);
            return redirect()->route('paper-questions.index')
                ->with('success', 'Paper Question updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating paper question: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified paper question from storage.
     *
     * @param  PaperQuestion  $paperQuestion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PaperQuestion $paperQuestion)
    {
        try {
            $this->paperQuestionService->deletePaperQuestion($paperQuestion);
            return redirect()->route('paper-questions.index')
                ->with('success', 'Question removed from paper successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error removing question from paper: ' . $e->getMessage());
        }
    }

    /**
     * Display questions for a specific paper.
     * 
     * @param Paper $paper
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function paperQuestions(Paper $paper, Request $request)
    {
        $cacheKey = "paper:{$paper->id}:questions";
        
        $paperQuestions = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($paper) {
            return $this->paperQuestionService->getQuestionsForPaper($paper);
        });
        
        if ($request->expectsJson()) {
            return PaperQuestionResource::collection($paperQuestions);
        }
        
        return view('paper_questions.paper_questions', compact('paperQuestions', 'paper'));
    }

    /**
     * Show form to add multiple questions to a paper.
     * 
     * @param Paper $paper
     * @return \Illuminate\View\View
     */
    public function addQuestions(Paper $paper)
    {
        // Get IDs of questions already in the paper
        $existingQuestionIds = $paper->questions->pluck('id')->toArray();
        
        // Get available questions, excluding those already in paper
        $questions = Question::with('subject')
            ->whereNotIn('id', $existingQuestionIds)
            ->get();
            
        return view('paper_questions.add_questions', compact('paper', 'questions'));
    }
    
    /**
     * Store multiple questions for a paper at once.
     *
     * @param Request $request
     * @param Paper $paper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function storeMultiple(Request $request, Paper $paper)
    {
        $data = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);
        
        try {
            $count = $this->paperQuestionService->addMultipleQuestions(
                $paper, 
                $data['question_ids']
            );
            
            // Clear cache
            $this->clearPaperQuestionCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} questions added to paper successfully!",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('success', "{$count} questions added to paper successfully!");
        } catch (\Exception $e) {
            Log::error('Error adding multiple questions to paper', [
                'paper_id' => $paper->id,
                'question_ids' => $data['question_ids'],
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding questions: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding questions: ' . $e->getMessage());
        }
    }

    /**
     * Update question order for a paper.
     * 
     * @param Request $request
     * @param Paper $paper
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, Paper $paper)
    {
        $data = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:paper_questions,id',
            'questions.*.order' => 'required|integer|min:1'
        ]);
        
        try {
            $this->paperQuestionService->reorderQuestions($paper, $data['questions']);
            
            // Clear cache
            $this->clearPaperQuestionCaches($paper->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Question order updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering questions', [
                'paper_id' => $paper->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear paper question related caches.
     *
     * @param int $paperId
     * @return void
     */
    protected function clearPaperQuestionCaches($paperId = null)
    {
        Cache::forget('paper_questions:list');
        
        if ($paperId) {
            Cache::forget("paper:{$paperId}:questions");
            Cache::forget("paper:{$paperId}:details");
        }
    }
}
