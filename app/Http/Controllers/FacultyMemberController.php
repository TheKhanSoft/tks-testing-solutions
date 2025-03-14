<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacultyMemberFormRequest;
use App\Services\FacultyMemberService;
use App\Models\FacultyMember;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\Request;

class FacultyMemberController extends Controller
{
    protected $facultyMemberService;

    public function __construct(FacultyMemberService $facultyMemberService)
    {
        $this->facultyMemberService = $facultyMemberService;
        $this->middleware('permission:view-faculty-members')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-faculty-members')->only(['create', 'store']);
        $this->middleware('permission:edit-faculty-members')->only(['edit', 'update']);
        $this->middleware('permission:delete-faculty-members')->only('destroy');
    }

    /**
     * Display a listing of faculty members.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = $request->only(['department_id']);
        $facultyMembers = $this->facultyMemberService->getPaginatedFacultyMembers($filters);
        $departments = Department::all();
        
        return view('faculty_members.index', compact('facultyMembers', 'departments', 'filters'));
    }

    /**
     * Show the form for creating a new faculty member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = Department::all();
        return view('faculty_members.create', compact('departments'));
    }

    /**
     * Store a newly created faculty member in storage.
     *
     * @param  FacultyMemberFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(FacultyMemberFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $this->facultyMemberService->createFacultyMember($validatedData);
            return redirect()->route('faculty-members.index')
                ->with('success', 'Faculty Member created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\View\View
     */
    public function show(FacultyMember $facultyMember)
    {
        // Eager load related models to prevent N+1 query problem
        $facultyMember->load(['department', 'subjects']);
        return view('faculty_members.show', compact('facultyMember'));
    }

    /**
     * Show the form for editing the specified faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\View\View
     */
    public function edit(FacultyMember $facultyMember)
    {
        $departments = Department::all();
        return view('faculty_members.edit', compact('facultyMember', 'departments'));
    }

    /**
     * Update the specified faculty member in storage.
     *
     * @param  FacultyMemberFormRequest  $request
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(FacultyMemberFormRequest $request, FacultyMember $facultyMember)
    {
        $validatedData = $request->validated();
        
        try {
            $this->facultyMemberService->updateFacultyMember($facultyMember, $validatedData);
            return redirect()->route('faculty-members.index')
                ->with('success', 'Faculty Member updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified faculty member from storage.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FacultyMember $facultyMember)
    {
        try {
            $this->facultyMemberService->deleteFacultyMember($facultyMember);
            return redirect()->route('faculty-members.index')
                ->with('success', 'Faculty Member deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of faculty members matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $facultyMembers = $this->facultyMemberService->searchFacultyMembers($searchTerm);
        $departments = Department::all();
        
        return view('faculty_members.index', compact('facultyMembers', 'searchTerm', 'departments'));
    }
    
    /**
     * Show form to assign subjects to a faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\View\View
     */
    public function assignSubjects(FacultyMember $facultyMember)
    {
        $assignedSubjects = $facultyMember->subjects->pluck('id')->toArray();
        $availableSubjects = Subject::whereNotIn('id', $assignedSubjects)->get();
        
        return view('faculty_members.assign_subjects', compact('facultyMember', 'availableSubjects', 'assignedSubjects'));
    }
}