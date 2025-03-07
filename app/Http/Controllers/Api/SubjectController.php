<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectFormRequest;
use App\Services\SubjectService;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    protected $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * Display a listing of subjects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $subjects = $this->subjectService->getPaginatedSubjects();
        return response()->json(['data' => $subjects, 'message' => 'Subjects retrieved successfully'], 200);
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SubjectFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $subject = $this->subjectService->createSubject($validatedData);

        return response()->json(['data' => $subject, 'message' => 'Subject created successfully'], 201);
    }

    /**
     * Display the specified subject.
     *
     * @param  Subject  $subject
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Subject $subject): JsonResponse
    {
        return response()->json(['data' => $subject, 'message' => 'Subject retrieved successfully'], 200);
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @param  Subject  $subject
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SubjectFormRequest $request, Subject $subject): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedSubject = $this->subjectService->updateSubject($subject, $validatedData);

        return response()->json(['data' => $updatedSubject, 'message' => 'Subject updated successfully'], 200);
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  Subject  $subject
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Subject $subject): JsonResponse
    {
        $this->subjectService->deleteSubject($subject);

        return response()->json(['message' => 'Subject deleted successfully'], 200);
    }

    /**
     * Display a listing of subjects matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $subjects = $this->subjectService->searchSubjects($searchTerm);
        return response()->json(['data' => $subjects, 'message' => 'Subjects retrieved successfully'], 200);
    }
}