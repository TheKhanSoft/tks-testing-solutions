<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionTypeFormRequest;
use App\Services\QuestionTypeService;
use App\Models\QuestionType;
use Illuminate\Http\Request;

class QuestionTypeController extends Controller
{
    protected $questionTypeService;

    public function __construct(QuestionTypeService $questionTypeService)
    {
        $this->questionTypeService = $questionTypeService;
    }

    /**
     * Display a listing of question types.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $questionTypes = $this->questionTypeService->getPaginatedQuestionTypes();
        return view('question_types.index', compact('questionTypes')); // Assuming you have a question_types.index view
    }

    /**
     * Show the form for creating a new question type.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('question_types.create'); // Assuming you have a question_types.create view
    }

    /**
     * Store a newly created question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(QuestionTypeFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->questionTypeService->createQuestionType($validatedData);

        return redirect()->route('question-types.index')->with('success', 'Question Type created successfully!');
    }

    /**
     * Display the specified question type.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\View\View
     */
    public function show(QuestionType $questionType)
    {
        return view('question_types.show', compact('questionType')); // Assuming you have a question_types.show view
    }

    /**
     * Show the form for editing the specified question type.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\View\View
     */
    public function edit(QuestionType $questionType)
    {
        return view('question_types.edit', compact('questionType')); // Assuming you have a question_types.edit view
    }

    /**
     * Update the specified question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(QuestionTypeFormRequest $request, QuestionType $questionType)
    {
        $validatedData = $request->validated();
        $this->questionTypeService->updateQuestionType($questionType, $validatedData);

        return redirect()->route('question-types.index')->with('success', 'Question Type updated successfully!');
    }

    /**
     * Remove the specified question type from storage.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(QuestionType $questionType)
    {
        $this->questionTypeService->deleteQuestionType($questionType);

        return redirect()->route('question-types.index')->with('success', 'Question Type deleted successfully!');
    }

    /**
     * Display a listing of question types matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $questionTypes = $this->questionTypeService->searchQuestionTypes($searchTerm);
        return view('question_types.index', compact('questionTypes', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}