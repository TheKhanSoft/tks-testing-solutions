<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionFormRequest;
use App\Services\QuestionService;
use App\Models\Question;
use App\Models\Subject; // Import Subject model
use App\Models\QuestionType; // Import QuestionType model
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Display a listing of questions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $questions = $this->questionService->getPaginatedQuestions();
        return view('questions.index', compact('questions')); // Assuming you have a questions.index view
    }

    /**
     * Show the form for creating a new question.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $subjects = Subject::all(); // Fetch subjects for dropdown
        $questionTypes = QuestionType::all(); // Fetch question types for dropdown
        return view('questions.create', compact('subjects', 'questionTypes')); // Assuming you have a questions.create view
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
        $this->questionService->createQuestion($validatedData);

        return redirect()->route('questions.index')->with('success', 'Question created successfully!');
    }

    /**
     * Display the specified question.
     *
     * @param  Question  $question
     * @return \Illuminate\View\View
     */
    public function show(Question $question)
    {
        return view('questions.show', compact('question')); // Assuming you have a questions.show view
    }

    /**
     * Show the form for editing the specified question.
     *
     * @param  Question  $question
     * @return \Illuminate\View\View
     */
    public function edit(Question $question)
    {
        $subjects = Subject::all(); // Fetch subjects for dropdown
        $questionTypes = QuestionType::all(); // Fetch question types for dropdown
        return view('questions.edit', compact('question', 'subjects', 'questionTypes')); // Assuming you have a questions.edit view
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
        $this->questionService->updateQuestion($question, $validatedData);

        return redirect()->route('questions.index')->with('success', 'Question updated successfully!');
    }

    /**
     * Remove the specified question from storage.
     *
     * @param  Question  $question
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Question $question)
    {
        $this->questionService->deleteQuestion($question);

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully!');
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
        return view('questions.index', compact('questions', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}