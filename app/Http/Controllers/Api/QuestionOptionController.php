<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionOptionFormRequest;
use App\Services\QuestionOptionService;
use App\Models\QuestionOption;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $questionOptions = $this->questionOptionService->getPaginatedQuestionOptions();
        return response()->json(['data' => $questionOptions, 'message' => 'Question Options retrieved successfully'], 200);
    }

    /**
     * Store a newly created question option in storage.
     *
     * @param  QuestionOptionFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(QuestionOptionFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $questionOption = $this->questionOptionService->createQuestionOption($validatedData);

        return response()->json(['data' => $questionOption, 'message' => 'Question Option created successfully'], 201);
    }

    /**
     * Display the specified question option.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(QuestionOption $questionOption): JsonResponse
    {
        return response()->json(['data' => $questionOption, 'message' => 'Question Option retrieved successfully'], 200);
    }

    /**
     * Update the specified question option in storage.
     *
     * @param  QuestionOptionFormRequest  $request
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(QuestionOptionFormRequest $request, QuestionOption $questionOption): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedQuestionOption = $this->questionOptionService->updateQuestionOption($questionOption, $validatedData);

        return response()->json(['data' => $updatedQuestionOption, 'message' => 'Question Option updated successfully'], 200);
    }

    /**
     * Remove the specified question option from storage.
     *
     * @param  QuestionOption  $questionOption
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(QuestionOption $questionOption): JsonResponse
    {
        $this->questionOptionService->deleteQuestionOption($questionOption);

        return response()->json(['message' => 'Question Option deleted successfully'], 200);
    }
}