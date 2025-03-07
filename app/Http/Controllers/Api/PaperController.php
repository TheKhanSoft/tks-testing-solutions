<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaperFormRequest;
use App\Services\PaperService;
use App\Models\Paper;
use App\Models\Subject;
use App\Models\PaperCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaperController extends Controller
{
    protected $paperService;

    public function __construct(PaperService $paperService)
    {
        $this->paperService = $paperService;
    }

    /**
     * Display a listing of papers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $papers = $this->paperService->getPaginatedPapers();
        return response()->json(['data' => $papers, 'message' => 'Papers retrieved successfully'], 200);
    }

    /**
     * Store a newly created paper in storage.
     *
     * @param  PaperFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaperFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $paper = $this->paperService->createPaper($validatedData);

        return response()->json(['data' => $paper, 'message' => 'Paper created successfully'], 201);
    }

    /**
     * Display the specified paper.
     *
     * @param  Paper  $paper
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Paper $paper): JsonResponse
    {
        return response()->json(['data' => $paper, 'message' => 'Paper retrieved successfully'], 200);
    }

    /**
     * Update the specified paper in storage.
     *
     * @param  PaperFormRequest  $request
     * @param  Paper  $paper
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PaperFormRequest $request, Paper $paper): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedPaper = $this->paperService->updatePaper($paper, $validatedData);

        return response()->json(['data' => $updatedPaper, 'message' => 'Paper updated successfully'], 200);
    }

    /**
     * Remove the specified paper from storage.
     *
     * @param  Paper  $paper
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Paper $paper): JsonResponse
    {
        $this->paperService->deletePaper($paper);

        return response()->json(['message' => 'Paper deleted successfully'], 200);
    }
}