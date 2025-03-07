<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentFormRequest;
use App\Services\DepartmentService;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of departments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $departments = $this->departmentService->getPaginatedDepartments();
        return response()->json(['data' => $departments, 'message' => 'Departments retrieved successfully'], 200);
    }

    /**
     * Store a newly created department in storage.
     *
     * @param  DepartmentFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DepartmentFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $department = $this->departmentService->createDepartment($validatedData);

        return response()->json(['data' => $department, 'message' => 'Department created successfully'], 201);
    }

    /**
     * Display the specified department.
     *
     * @param  Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Department $department): JsonResponse
    {
        return response()->json(['data' => $department, 'message' => 'Department retrieved successfully'], 200);
    }

    /**
     * Update the specified department in storage.
     *
     * @param  DepartmentFormRequest  $request
     * @param  Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DepartmentFormRequest $request, Department $department): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedDepartment = $this->departmentService->updateDepartment($department, $validatedData);

        return response()->json(['data' => $updatedDepartment, 'message' => 'Department updated successfully'], 200);
    }

    /**
     * Remove the specified department from storage.
     *
     * @param  Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->departmentService->deleteDepartment($department);

        return response()->json(['message' => 'Department deleted successfully'], 200);
    }

    /**
     * Display a listing of departments matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $departments = $this->departmentService->searchDepartments($searchTerm);
        return response()->json(['data' => $departments, 'message' => 'Departments retrieved successfully'], 200);
    }
}