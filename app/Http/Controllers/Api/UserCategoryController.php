<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserCategoryFormRequest;
use App\Services\UserCategoryService;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserCategoryController extends Controller
{
    protected $userCategoryService;

    public function __construct(UserCategoryService $userCategoryService)
    {
        $this->userCategoryService = $userCategoryService;
    }

    /**
     * Display a listing of user categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $userCategories = $this->userCategoryService->getPaginatedUserCategories();
        return response()->json(['data' => $userCategories, 'message' => 'User Categories retrieved successfully'], 200);
    }

    /**
     * Store a newly created user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserCategoryFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $userCategory = $this->userCategoryService->createUserCategory($validatedData);

        return response()->json(['data' => $userCategory, 'message' => 'User Category created successfully'], 201);
    }

    /**
     * Display the specified user category.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(UserCategory $userCategory): JsonResponse
    {
        return response()->json(['data' => $userCategory, 'message' => 'User Category retrieved successfully'], 200);
    }

    /**
     * Update the specified user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserCategoryFormRequest $request, UserCategory $userCategory): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedUserCategory = $this->userCategoryService->updateUserCategory($userCategory, $validatedData);

        return response()->json(['data' => $updatedUserCategory, 'message' => 'User Category updated successfully'], 200);
    }

    /**
     * Remove the specified user category from storage.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(UserCategory $userCategory): JsonResponse
    {
        $this->userCategoryService->deleteUserCategory($userCategory);

        return response()->json(['message' => 'User Category deleted successfully'], 200);
    }
}