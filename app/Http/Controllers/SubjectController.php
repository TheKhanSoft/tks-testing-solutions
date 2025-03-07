<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectFormRequest;
use App\Services\SubjectService;
use App\Models\Subject;
use Illuminate\Http\Request;

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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $subjects = $this->subjectService->getPaginatedSubjects();
        return view('subjects.index', compact('subjects')); // Assuming you have a subjects.index view
    }

    /**
     * Show the form for creating a new subject.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('subjects.create'); // Assuming you have a subjects.create view
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SubjectFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->subjectService->createSubject($validatedData);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully!');
    }

    /**
     * Display the specified subject.
     *
     * @param  Subject  $subject
     * @return \Illuminate\View\View
     */
    public function show(Subject $subject)
    {
        return view('subjects.show', compact('subject')); // Assuming you have a subjects.show view
    }

    /**
     * Show the form for editing the specified subject.
     *
     * @param  Subject  $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject')); // Assuming you have a subjects.edit view
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  SubjectFormRequest  $request
     * @param  Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SubjectFormRequest $request, Subject $subject)
    {
        $validatedData = $request->validated();
        $this->subjectService->updateSubject($subject, $validatedData);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully!');
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subject $subject)
    {
        $this->subjectService->deleteSubject($subject);

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully!');
    }

    /**
     * Display a listing of subjects matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $subjects = $this->subjectService->searchSubjects($searchTerm);
        return view('subjects.index', compact('subjects', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}