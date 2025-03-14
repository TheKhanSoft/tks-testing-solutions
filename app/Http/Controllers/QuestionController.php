<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionFormRequest;
use App\Services\QuestionService;
use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
        $this->middleware('permission:view-questions')->only(['index', 'show', 'search', 'filter']);
        $this->middleware('permission:create-questions')->only(['create', 'store', 'createBulk', 'storeBulk']);
        $this->middleware('permission:edit-questions')->only(['edit', 'update']);
        $this->middleware('permission:delete-questions')->only('destroy');
    }

    /**
     * Display a listing of questions.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get filter parameters from request
        $filters = $request->only(['subject_id', 'question_type_id', 'difficulty_level', 'status']);
        
        $questions = $this->questionService->getPaginatedQuestions($filters);
        $subjects = Subject::all();
        $questionTypes = QuestionType::all();
        
        return view('questions.index', compact(
            'questions',
            'subjects',
            'questionTypes',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new question.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $subjects = Subject::all();
        $questionTypes = QuestionType::all();
        return view('questions.create', compact('subjects', 'questionTypes'));
    }

    /**
     * Store a newly created question in storage.
     *
     * @param  QuestionFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(QuestionFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $question = $this->questionService->createQuestion($validatedData);
            
            if ($request->has('add_options') && $request->add_options) {
                return redirect()->route('question-options.create-for-question', $question)
                    ->with('success', 'Question created successfully! Now add options.');
            }
            
            return redirect()->route('questions.index')
                ->with('success', 'Question created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating question: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified question.
     *
     * @param  Question  $question
     * @return \Illuminate\View\View
     */
    public function show(Question $question)
    {
        // Eager load related data to avoid N+1 query problems
        $question->load(['subject', 'questionType', 'options' => function($query) {
            $query->orderBy('order');
        }]);
        
        return view('questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     *
     * @param  Question  $question
     * @return \Illuminate\View\View
     */
    public function edit(Question $question)
    {
        $subjects = Subject::all();
        $questionTypes = QuestionType::all();
        
        // Load options for this question
        $question->load('options');
        
        return view('questions.edit', compact('question', 'subjects', 'questionTypes'));
    }

    /**
     * Update the specified question in storage.
     *
     * @param  QuestionFormRequest  $request
     * @param  Question  $question
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(QuestionFormRequest $request, Question $question)
    {
        $validatedData = $request->validated();
        
        try {
            $this->questionService->updateQuestion($question, $validatedData);
            
            if ($request->has('edit_options') && $request->edit_options) {
                return redirect()->route('questions.show', $question)
                    ->with('success', 'Question updated successfully!');
            }
            
            return redirect()->route('questions.index')
                ->with('success', 'Question updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating question: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question from storage.
     *
     * @param  Question  $question
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Question $question)
    {
        try {
            $this->questionService->deleteQuestion($question);
            return redirect()->route('questions.index')
                ->with('success', 'Question deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting question: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of questions matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $questions = $this->questionService->searchQuestions($searchTerm);
        
        $subjects = Subject::all();
        $questionTypes = QuestionType::all();
        
        return view('questions.index', compact(
            'questions',
            'searchTerm',
            'subjects',
            'questionTypes'
        ));
    }
    
    /**
     * Show the form for creating multiple questions at once.
     *
     * @return \Illuminate\View\View
     */
    public function createBulk()
    {
        $subjects = Subject::all();
        $questionTypes = QuestionType::all();
        return view('questions.create_bulk', compact('subjects', 'questionTypes'));
    }
    
    /**
     * Store multiple questions at once.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'questions' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'question_type_id' => 'required|exists:question_types,id',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'marks' => 'required|integer|min:1'
        ]);
        
        try {
            $count = $this->questionService->createBulkQuestions(
                $request->input('questions'),
                $request->only(['subject_id', 'question_type_id', 'difficulty_level', 'marks'])
            );
            
            return redirect()->route('questions.index')
                ->with('success', $count . ' questions created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating bulk questions: ' . $e->getMessage());
        }
    }
}