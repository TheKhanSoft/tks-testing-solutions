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
        $this->middleware('permission:view-departments')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-departments')->only(['create', 'store']);
        $this->middleware('permission:edit-departments')->only(['edit', 'update']);
        $this->middleware('permission:delete-departments')->only('destroy');
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
        
        try {
            $this->departmentService->createDepartment($validatedData);
            return redirect()->route('departments.index')
                ->with('success', 'Department created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating department: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified department.
     *
     * @param  Department  $department
     * @return \Illuminate\View\View
     */
    public function show(Department $department)
    {
        // Eager load related models to avoid N+1 query problem
        $department->load([
            'facultyMembers',
            'subjects' => function($query) {
                $query->withCount('questions');
            }
        ]);
        
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
        
        try {
            $this->departmentService->updateDepartment($department, $validatedData);
            return redirect()->route('departments.index')
                ->with('success', 'Department updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating department: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified department from storage.
     *
     * @param  Department  $department
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Department $department)
    {
        try {
            $this->departmentService->deleteDepartment($department);
            return redirect()->route('departments.index')
                ->with('success', 'Department deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting department: ' . $e->getMessage());
        }
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
    
    /**
     * Get faculty members for a department.
     *
     * @param  Department  $department
     * @return \Illuminate\View\View
     */
    public function facultyMembers(Department $department)
    {
        $facultyMembers = $department->facultyMembers()->paginate(15);
        return view('departments.faculty_members', compact('department', 'facultyMembers'));
    }
    
    /**
     * Get subjects for a department.
     *
     * @param  Department  $department
     * @return \Illuminate\View\View
     */
    public function subjects(Department $department)
    {
        $subjects = $department->subjects()->paginate(15);
        return view('departments.subjects', compact('department', 'subjects'));
    }
}