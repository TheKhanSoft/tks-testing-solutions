<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionOptionFormRequest;
use App\Services\QuestionOptionService;
use App\Models\QuestionOption;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    protected $questionOptionService;

    public function __construct(QuestionOptionService $questionOptionService)
    {
        $this->questionOptionService = $questionOptionService;
        $this->middleware('permission:view-question-options')->only(['index', 'show']);
        $this->middleware('permission:create-question-options')->only(['create', 'store', 'createForQuestion']);
        $this->middleware('permission:edit-question-options')->only(['edit', 'update', 'reorder']);
        $this->middleware('permission:delete-question-options')->only('destroy');
    }

    /**
     * Display a listing of question options.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $questionOptions = $this->questionOptionService->getPaginatedQuestionOptions();
        return view('question_options.index', compact('questionOptions'));
    }

    /**
     * Show the form for creating a new question option.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $questions = Question::with('subject')->get();
        return view('question_options.create', compact('questions'));
    }

    /**
     * Store a newly created question option in storage.
     *
     * @param  QuestionOptionFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(QuestionOptionFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $this->questionOptionService->createQuestionOption($validatedData);
            
            if ($request->has('redirect_to_question') && $request->redirect_to_question) {
                return redirect()->route('questions.show', $validatedData['question_id'])
                    ->with('success', 'Question Option created successfully!');
            }
            
            return redirect()->route('question-options.index')
                ->with('success', 'Question Option created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating question option: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified question option.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\View\View
     */
    public function show(QuestionOption $questionOption)
    {
        $questionOption->load('question.subject');
        return view('question_options.show', compact('questionOption'));
    }

    /**
     * Show the form for editing the specified question option.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\View\View
     */
    public function edit(QuestionOption $questionOption)
    {
        $questions = Question::with('subject')->get();
        return view('question_options.edit', compact('questionOption', 'questions'));
    }

    /**
     * Update the specified question option in storage.
     *
     * @param  QuestionOptionFormRequest  $request
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(QuestionOptionFormRequest $request, QuestionOption $questionOption)
    {
        $validatedData = $request->validated();
        
        try {
            $this->questionOptionService->updateQuestionOption($questionOption, $validatedData);
            
            if ($request->has('redirect_to_question') && $request->redirect_to_question) {
                return redirect()->route('questions.show', $questionOption->question_id)
                    ->with('success', 'Question Option updated successfully!');
            }
            
            return redirect()->route('question-options.index')
                ->with('success', 'Question Option updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating question option: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question option from storage.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(QuestionOption $questionOption)
    {
        $questionId = $questionOption->question_id;
        $redirectToQuestion = request('redirect_to_question', false);
        
        try {
            $this->questionOptionService->deleteQuestionOption($questionOption);
            
            if ($redirectToQuestion) {
                return redirect()->route('questions.show', $questionId)
                    ->with('success', 'Question Option deleted successfully!');
            }
            
            return redirect()->route('question-options.index')
                ->with('success', 'Question Option deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting question option: ' . $e->getMessage());
        }
    }
    
    /**
     * Show form for creating options for a specific question.
     * 
     * @param Question $question
     * @return \Illuminate\View\View
     */
    public function createForQuestion(Question $question)
    {
        return view('question_options.create_for_question', compact('question'));
    }
    
    /**
     * Update option order for a question.
     * 
     * @param Request $request
     * @param Question $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, Question $question)
    {
        try {
            $this->questionOptionService->reorderOptions($question, $request->input('options'));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}