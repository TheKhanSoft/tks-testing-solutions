<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperFormRequest;
use App\Services\PaperService;
use App\Models\Paper;
use App\Models\Subject; // Import Subject model
use App\Models\PaperCategory; // Import PaperCategory model
use Illuminate\Http\Request;

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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $papers = $this->paperService->getPaginatedPapers();
        return view('papers.index', compact('papers')); // Assuming you have a papers.index view
    }

    /**
     * Show the form for creating a new paper.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $subjects = Subject::all(); // Fetch subjects for dropdown
        $paperCategories = PaperCategory::all(); // Fetch paper categories for dropdown
        return view('papers.create', compact('subjects', 'paperCategories')); // Assuming you have a papers.create view
    }

    /**
     * Store a newly created paper in storage.
     *
     * @param  PaperFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PaperFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->paperService->createPaper($validatedData);

        return redirect()->route('papers.index')->with('success', 'Paper created successfully!');
    }

    /**
     * Display the specified paper.
     *
     * @param  Paper  $paper
     * @return \Illuminate\View\View
     */
    public function show(Paper $paper)
    {
        return view('papers.show', compact('paper')); // Assuming you have a papers.show view
    }

    /**
     * Show the form for editing the specified paper.
     *
     * @param  Paper  $paper
     * @return \Illuminate\View\View
     */
    public function edit(Paper $paper)
    {
        $subjects = Subject::all(); // Fetch subjects for dropdown
        $paperCategories = PaperCategory::all(); // Fetch paper categories for dropdown
        return view('papers.edit', compact('paper', 'subjects', 'paperCategories')); // Assuming you have a papers.edit view
    }

    /**
     * Update the specified paper in storage.
     *
     * @param  PaperFormRequest  $request
     * @param  Paper  $paper
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PaperFormRequest $request, Paper $paper)
    {
        $validatedData = $request->validated();
        $this->paperService->updatePaper($paper, $validatedData);

        return redirect()->route('papers.index')->with('success', 'Paper updated successfully!');
    }

    /**
     * Remove the specified paper from storage.
     *
     * @param  Paper  $paper
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Paper $paper)
    {
        $this->paperService->deletePaper($paper);

        return redirect()->route('papers.index')->with('success', 'Paper deleted successfully!');
    }

    /**
     * Display a listing of papers matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $papers = $this->paperService->searchPapers($searchTerm);
        return view('papers.index', compact('papers', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}