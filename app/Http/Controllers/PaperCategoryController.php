<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperCategoryFormRequest;
use App\Services\PaperCategoryService;
use App\Models\PaperCategory;
use Illuminate\Http\Request;

class PaperCategoryController extends Controller
{
    protected $paperCategoryService;

    public function __construct(PaperCategoryService $paperCategoryService)
    {
        $this->paperCategoryService = $paperCategoryService;
    }

    /**
     * Display a listing of paper categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $paperCategories = $this->paperCategoryService->getPaginatedPaperCategories();
        return view('paper_categories.index', compact('paperCategories')); // Assuming you have a paper_categories.index view
    }

    /**
     * Show the form for creating a new paper category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('paper_categories.create'); // Assuming you have a paper_categories.create view
    }

    /**
     * Store a newly created paper category in storage.
     *
     * @param  PaperCategoryFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PaperCategoryFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->paperCategoryService->createPaperCategory($validatedData);

        return redirect()->route('paper-categories.index')->with('success', 'Paper Category created successfully!');
    }

    /**
     * Display the specified paper category.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\View\View
     */
    public function show(PaperCategory $paperCategory)
    {
        return view('paper_categories.show', compact('paperCategory')); // Assuming you have a paper_categories.show view
    }

    /**
     * Show the form for editing the specified paper category.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\View\View
     */
    public function edit(PaperCategory $paperCategory)
    {
        return view('paper_categories.edit', compact('paperCategory')); // Assuming you have a paper_categories.edit view
    }

    /**
     * Update the specified paper category in storage.
     *
     * @param  PaperCategoryFormRequest  $request
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PaperCategoryFormRequest $request, PaperCategory $paperCategory)
    {
        $validatedData = $request->validated();
        $this->paperCategoryService->updatePaperCategory($paperCategory, $validatedData);

        return redirect()->route('paper-categories.index')->with('success', 'Paper Category updated successfully!');
    }

    /**
     * Remove the specified paper category from storage.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PaperCategory $paperCategory)
    {
        $this->paperCategoryService->deletePaperCategory($paperCategory);

        return redirect()->route('paper-categories.index')->with('success', 'Paper Category deleted successfully!');
    }

    /**
     * Display a listing of paper categories matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $paperCategories = $this->paperCategoryService->searchPaperCategories($searchTerm);
        return view('paper_categories.index', compact('paperCategories', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}