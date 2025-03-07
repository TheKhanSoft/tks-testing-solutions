<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerFormRequest; // You might need to create this form request
use App\Services\AnswerService;
use App\Models\Answer;
use App\Models\TestAttempt; // Import TestAttempt model
use App\Models\Question; // Import Question model
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    protected $answerService;

    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
     * Display a listing of answers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $answers = $this->answerService->getPaginatedAnswers();
        return view('answers.index', compact('answers')); // Assuming you have an answers.index view
    }

    /**
     * Show the form for creating a new answer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $testAttempts = TestAttempt::all(); // Fetch test attempts for dropdown
        $questions = Question::all(); // Fetch questions for dropdown
        return view('answers.create', compact('testAttempts', 'questions')); // Assuming you have an answers.create view
    }

    /**
     * Store a newly created answer in storage.
     *
     * @param  AnswerFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AnswerFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->answerService->createAnswer($validatedData);

        return redirect()->route('answers.index')->with('success', 'Answer created successfully!');
    }

    /**
     * Display the specified answer.
     *
     * @param  Answer  $answer
     * @return \Illuminate\View\View
     */
    public function show(Answer $answer)
    {
        return view('answers.show', compact('answer')); // Assuming you have an answers.show view
    }

    /**
     * Show the form for editing the specified answer.
     *
     * @param  Answer  $answer
     * @return \Illuminate\View\View
     */
    public function edit(Answer $answer)
    {
        $testAttempts = TestAttempt::all(); // Fetch test attempts for dropdown
        $questions = Question::all(); // Fetch questions for dropdown
        return view('answers.edit', compact('answer', 'testAttempts', 'questions')); // Assuming you have an answers.edit view
    }

    /**
     * Update the specified answer in storage.
     *
     * @param  AnswerFormRequest  $request
     * @param  Answer  $answer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AnswerFormRequest $request, Answer $answer)
    {
        $validatedData = $request->validated();
        $this->answerService->updateAnswer($answer, $validatedData);

        return redirect()->route('answers.index')->with('success', 'Answer updated successfully!');
    }

    /**
     * Remove the specified answer from storage.
     *
     * @param  Answer  $answer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Answer $answer)
    {
        $this->answerService->deleteAnswer($answer);

        return redirect()->route('answers.index')->with('success', 'Answer deleted successfully!');
    }
}