<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionOptionFormRequest;
use App\Services\QuestionOptionService;
use App\Models\QuestionOption;
use App\Models\Question; // Import Question model
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    protected $questionOptionService;

    public function __construct(QuestionOptionService $questionOptionService)
    {
        $this->questionOptionService = $questionOptionService;
    }

    /**
     * Display a listing of question options.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $questionOptions = $this->questionOptionService->getPaginatedQuestionOptions();
        return view('question_options.index', compact('questionOptions')); // Assuming you have a question_options.index view
    }

    /**
     * Show the form for creating a new question option.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $questions = Question::all(); // Fetch questions for dropdown
        return view('question_options.create', compact('questions')); // Assuming you have a question_options.create view
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
        $this->questionOptionService->createQuestionOption($validatedData);

        return redirect()->route('question-options.index')->with('success', 'Question Option created successfully!');
    }

    /**
     * Display the specified question option.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\View\View
     */
    public function show(QuestionOption $questionOption)
    {
        return view('question_options.show', compact('questionOption')); // Assuming you have a question_options.show view
    }

    /**
     * Show the form for editing the specified question option.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\View\View
     */
    public function edit(QuestionOption $questionOption)
    {
        $questions = Question::all(); // Fetch questions for dropdown
        return view('question_options.edit', compact('questionOption', 'questions')); // Assuming you have a question_options.edit view
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
        $this->questionOptionService->updateQuestionOption($questionOption, $validatedData);

        return redirect()->route('question-options.index')->with('success', 'Question Option updated successfully!');
    }

    /**
     * Remove the specified question option from storage.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(QuestionOption $questionOption)
    {
        $this->questionOptionService->deleteQuestionOption($questionOption);

        return redirect()->route('question-options.index')->with('success', 'Question Option deleted successfully!');
    }
}