<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaperCategoryFormRequest;
use App\Services\PaperCategoryService;
use App\Models\PaperCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaperCategoryController extends Controller
{
    protected $paperCategoryService;

    public function __construct(PaperCategoryService $paperCategoryService)
    {
        $this->paperCategoryService = $paperCategoryService;
    }

    /**
     * Display a listing of paper categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $paperCategories = $this->paperCategoryService->getPaginatedPaperCategories();
        return response()->json(['data' => $paperCategories, 'message' => 'Paper Categories retrieved successfully'], 200);
    }

    /**
     * Store a newly created paper category in storage.
     *
     * @param  PaperCategoryFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaperCategoryFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $paperCategory = $this->paperCategoryService->createPaperCategory($validatedData);

        return response()->json(['data' => $paperCategory, 'message' => 'Paper Category created successfully'], 201);
    }

    /**
     * Display the specified paper category.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(PaperCategory $paperCategory): JsonResponse
    {
        return response()->json(['data' => $paperCategory, 'message' => 'Paper Category retrieved successfully'], 200);
    }

    /**
     * Update the specified paper category in storage.
     *
     * @param  PaperCategoryFormRequest  $request
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PaperCategoryFormRequest $request, PaperCategory $paperCategory): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedPaperCategory = $this->paperCategoryService->updatePaperCategory($paperCategory, $validatedData);

        return response()->json(['data' => $updatedPaperCategory, 'message' => 'Paper Category updated successfully'], 200);
    }

    /**
     * Remove the specified paper category from storage.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PaperCategory $paperCategory): JsonResponse
    {
        $this->paperCategoryService->deletePaperCategory($paperCategory);

        return response()->json(['message' => 'Paper Category deleted successfully'], 200);
    }
}