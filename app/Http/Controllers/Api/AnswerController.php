<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerFormRequest;
use App\Services\AnswerService;
use App\Models\Answer;
use App\Models\TestAttempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $answers = $this->answerService->getPaginatedAnswers();
        return response()->json(['data' => $answers, 'message' => 'Answers retrieved successfully'], 200);
    }

    /**
     * Store a newly created answer in storage.
     *
     * @param  AnswerFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AnswerFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $answer = $this->answerService->createAnswer($validatedData);

        return response()->json(['data' => $answer, 'message' => 'Answer created successfully'], 201);
    }

    /**
     * Display the specified answer.
     *
     * @param  Answer  $answer
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Answer $answer): JsonResponse
    {
        return response()->json(['data' => $answer, 'message' => 'Answer retrieved successfully'], 200);
    }

    /**
     * Update the specified answer in storage.
     *
     * @param  AnswerFormRequest  $request
     * @param  Answer  $answer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AnswerFormRequest $request, Answer $answer): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedAnswer = $this->answerService->updateAnswer($answer, $validatedData);

        return response()->json(['data' => $updatedAnswer, 'message' => 'Answer updated successfully'], 200);
    }

    /**
     * Remove the specified answer from storage.
     *
     * @param  Answer  $answer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Answer $answer): JsonResponse
    {
        $this->answerService->deleteAnswer($answer);

        return response()->json(['message' => 'Answer deleted successfully'], 200);
    }
}