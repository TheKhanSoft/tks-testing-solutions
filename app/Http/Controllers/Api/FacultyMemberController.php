<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacultyMemberFormRequest;
use App\Services\FacultyMemberService;
use App\Models\FacultyMember;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $facultyMembers = $this->facultyMemberService->getPaginatedFacultyMembers();
        return response()->json(['data' => $facultyMembers, 'message' => 'Faculty Members retrieved successfully'], 200);
    }

    /**
     * Store a newly created faculty member in storage.
     *
     * @param  FacultyMemberFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(FacultyMemberFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $facultyMember = $this->facultyMemberService->createFacultyMember($validatedData);

        return response()->json(['data' => $facultyMember, 'message' => 'Faculty Member created successfully'], 201);
    }

    /**
     * Display the specified faculty member.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(FacultyMember $facultyMember): JsonResponse
    {
        return response()->json(['data' => $facultyMember, 'message' => 'Faculty Member retrieved successfully'], 200);
    }

    /**
     * Update the specified faculty member in storage.
     *
     * @param  FacultyMemberFormRequest  $request
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(FacultyMemberFormRequest $request, FacultyMember $facultyMember): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedFacultyMember = $this->facultyMemberService->updateFacultyMember($facultyMember, $validatedData);

        return response()->json(['data' => $updatedFacultyMember, 'message' => 'Faculty Member updated successfully'], 200);
    }

    /**
     * Remove the specified faculty member from storage.
     *
     * @param  FacultyMember  $facultyMember
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(FacultyMember $facultyMember): JsonResponse
    {
        $this->facultyMemberService->deleteFacultyMember($facultyMember);

        return response()->json(['message' => 'Faculty Member deleted successfully'], 200);
    }

    /**
     * Display a listing of faculty members matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $facultyMembers = $this->facultyMemberService->searchFacultyMembers($searchTerm);
        return response()->json(['data' => $facultyMembers, 'message' => 'Faculty Members retrieved successfully'], 200);
    }
}