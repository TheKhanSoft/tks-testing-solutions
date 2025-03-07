<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacultyMemberFormRequest;
use App\Services\FacultyMemberService;
use App\Models\FacultyMember;
use App\Models\Department; // Import Department model
use Illuminate\Http\Request;

class FacultyMemberController extends Controller
{
    protected $facultyMemberService;

    public function __construct(FacultyMemberService $facultyMemberService)
    {
        $this->facultyMemberService = $facultyMemberService;
    }

    /**
     * Display a listing of faculty members.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $facultyMembers = $this->facultyMemberService->getPaginatedFacultyMembers();
        return view('faculty_members.index', compact('facultyMembers')); // Assuming you have a faculty_members.index view
    }

    /**
     * Show the form for creating a new faculty member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = Department::all(); // Fetch departments for dropdown
        return view('faculty_members.create', compact('departments')); // Assuming you have a faculty_members.create view
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
        $this->facultyMemberService->createFacultyMember($validatedData);

        return redirect()->route('faculty-members.index')->with('success', 'Faculty Member created successfully!');
    }

    /**
     * Display the specified faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\View\View
     */
    public function show(FacultyMember $facultyMember)
    {
        return view('faculty_members.show', compact('facultyMember')); // Assuming you have a faculty_members.show view
    }

    /**
     * Show the form for editing the specified faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\View\View
     */
    public function edit(FacultyMember $facultyMember)
    {
        $departments = Department::all(); // Fetch departments for dropdown
        return view('faculty_members.edit', compact('facultyMember', 'departments')); // Assuming you have a faculty_members.edit view
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
        $this->facultyMemberService->updateFacultyMember($facultyMember, $validatedData);

        return redirect()->route('faculty-members.index')->with('success', 'Faculty Member updated successfully!');
    }

    /**
     * Remove the specified faculty member from storage.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FacultyMember $facultyMember)
    {
        $this->facultyMemberService->deleteFacultyMember($facultyMember);

        return redirect()->route('faculty-members.index')->with('success', 'Faculty Member deleted successfully!');
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
        return view('faculty_members.index', compact('facultyMembers', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}