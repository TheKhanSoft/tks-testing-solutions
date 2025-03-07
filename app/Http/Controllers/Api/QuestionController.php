<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionFormRequest;
use App\Services\QuestionService;
use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $questions = $this->questionService->getPaginatedQuestions();
        return response()->json(['data' => $questions, 'message' => 'Questions retrieved successfully'], 200);
    }

    /**
     * Store a newly created question in storage.
     *
     * @param  QuestionFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(QuestionFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $question = $this->questionService->createQuestion($validatedData);

        return response()->json(['data' => $question, 'message' => 'Question created successfully'], 201);
    }

    /**
     * Display the specified question.
     *
     * @param  Question  $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Question $question): JsonResponse
    {
        return response()->json(['data' => $question, 'message' => 'Question retrieved successfully'], 200);
    }

    /**
     * Update the specified question in storage.
     *
     * @param  QuestionFormRequest  $request
     * @param  Question  $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(QuestionFormRequest $request, Question $question): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedQuestion = $this->questionService->updateQuestion($question, $validatedData);

        return response()->json(['data' => $updatedQuestion, 'message' => 'Question updated successfully'], 200);
    }

    /**
     * Remove the specified question from storage.
     *
     * @param  Question  $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Question $question): JsonResponse
    {
        $this->questionService->deleteQuestion($question);

        return response()->json(['message' => 'Question deleted successfully'], 200);
    }

    /**
     * Display a listing of questions matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $questions = $this->questionService->searchQuestions($searchTerm);
        return response()->json(['data' => $questions, 'message' => 'Questions retrieved successfully'], 200);
    }
}