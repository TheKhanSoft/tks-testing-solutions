<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentFormRequest;
use App\Services\DepartmentService;
use App\Models\Department;
use Illuminate\Http\Request;

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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $departments = $this->departmentService->getPaginatedDepartments();
        return view('departments.index', compact('departments')); // Assuming you have a departments.index view
    }

    /**
     * Show the form for creating a new department.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('departments.create'); // Assuming you have a departments.create view
    }

    /**
     * Store a newly created department in storage.
     *
     * @param  DepartmentFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(DepartmentFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->departmentService->createDepartment($validatedData);

        return redirect()->route('departments.index')->with('success', 'Department created successfully!');
    }

    /**
     * Display the specified department.
     *
     * @param  Department  $department
     * @return \Illuminate\View\View
     */
    public function show(Department $department)
    {
        return view('departments.show', compact('department')); // Assuming you have a departments.show view
    }

    /**
     * Show the form for editing the specified department.
     *
     * @param  Department  $department
     * @return \Illuminate\View\View
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department')); // Assuming you have a departments.edit view
    }

    /**
     * Update the specified department in storage.
     *
     * @param  DepartmentFormRequest  $request
     * @param  Department  $department
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(DepartmentFormRequest $request, Department $department)
    {
        $validatedData = $request->validated();
        $this->departmentService->updateDepartment($department, $validatedData);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department from storage.
     *
     * @param  Department  $department
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Department $department)
    {
        $this->departmentService->deleteDepartment($department);

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully!');
    }

    /**
     * Display a listing of departments matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $departments = $this->departmentService->searchDepartments($searchTerm);
        return view('departments.index', compact('departments', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}