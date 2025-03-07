<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestAttemptFormRequest;
use App\Services\TestAttemptService;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestAttemptController extends Controller
{
    protected $testAttemptService;

    public function __construct(TestAttemptService $testAttemptService)
    {
        $this->testAttemptService = $testAttemptService;
    }

    /**
     * Display a listing of test attempts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $testAttempts = $this->testAttemptService->getPaginatedTestAttempts();
        return response()->json(['data' => $testAttempts, 'message' => 'Test Attempts retrieved successfully'], 200);
    }

    /**
     * Store a newly created test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TestAttemptFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $testAttempt = $this->testAttemptService->createTestAttempt($validatedData);

        return response()->json(['data' => $testAttempt, 'message' => 'Test Attempt created successfully'], 201);
    }

    /**
     * Display the specified test attempt.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TestAttempt $testAttempt): JsonResponse
    {
        return response()->json(['data' => $testAttempt, 'message' => 'Test Attempt retrieved successfully'], 200);
    }

    /**
     * Update the specified test attempt in storage.
     *
     * @param  TestAttemptFormRequest  $request
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TestAttemptFormRequest $request, TestAttempt $testAttempt): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedTestAttempt = $this->testAttemptService->updateTestAttempt($testAttempt, $validatedData);

        return response()->json(['data' => $updatedTestAttempt, 'message' => 'Test Attempt updated successfully'], 200);
    }

    /**
     * Remove the specified test attempt from storage.
     *
     * @param  TestAttempt  $testAttempt
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TestAttempt $testAttempt): JsonResponse
    {
        $this->testAttemptService->deleteTestAttempt($testAttempt);

        return response()->json(['message' => 'Test Attempt deleted successfully'], 200);
    }
}