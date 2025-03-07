<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionTypeFormRequest;
use App\Services\QuestionTypeService;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $questionTypes = $this->questionTypeService->getPaginatedQuestionTypes();
        return response()->json(['data' => $questionTypes, 'message' => 'Question Types retrieved successfully'], 200);
    }

    /**
     * Store a newly created question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(QuestionTypeFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $questionType = $this->questionTypeService->createQuestionType($validatedData);

        return response()->json(['data' => $questionType, 'message' => 'Question Type created successfully'], 201);
    }

    /**
     * Display the specified question type.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(QuestionType $questionType): JsonResponse
    {
        return response()->json(['data' => $questionType, 'message' => 'Question Type retrieved successfully'], 200);
    }

    /**
     * Update the specified question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(QuestionTypeFormRequest $request, QuestionType $questionType): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedQuestionType = $this->questionTypeService->updateQuestionType($questionType, $validatedData);

        return response()->json(['data' => $updatedQuestionType, 'message' => 'Question Type updated successfully'], 200);
    }

    /**
     * Remove the specified question type from storage.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(QuestionType $questionType): JsonResponse
    {
        $this->questionTypeService->deleteQuestionType($questionType);

        return response()->json(['message' => 'Question Type deleted successfully'], 200);
    }

    /**
     * Display a listing of question types matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $questionTypes = $this->questionTypeService->searchQuestionTypes($searchTerm);
        return response()->json(['data' => $questionTypes, 'message' => 'Question Types retrieved successfully'], 200);
    }
}