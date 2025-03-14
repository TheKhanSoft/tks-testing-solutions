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
        $this->middleware('permission:view-paper-categories')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-paper-categories')->only(['create', 'store']);
        $this->middleware('permission:edit-paper-categories')->only(['edit', 'update']);
        $this->middleware('permission:delete-paper-categories')->only('destroy');
    }

    /**
     * Display a listing of paper categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $paperCategories = $this->paperCategoryService->getPaginatedPaperCategories();
        return view('paper_categories.index', compact('paperCategories'));
    }

    /**
     * Show the form for creating a new paper category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('paper_categories.create');
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
        
        try {
            $this->paperCategoryService->createPaperCategory($validatedData);
            return redirect()->route('paper-categories.index')
                ->with('success', 'Paper Category created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating paper category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified paper category.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\View\View
     */
    public function show(PaperCategory $paperCategory)
    {
        // Eager load related papers to avoid N+1 query problem
        $paperCategory->load('papers');
        return view('paper_categories.show', compact('paperCategory'));
    }

    /**
     * Show the form for editing the specified paper category.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\View\View
     */
    public function edit(PaperCategory $paperCategory)
    {
        return view('paper_categories.edit', compact('paperCategory'));
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
        
        try {
            $this->paperCategoryService->updatePaperCategory($paperCategory, $validatedData);
            return redirect()->route('paper-categories.index')
                ->with('success', 'Paper Category updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating paper category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified paper category from storage.
     *
     * @param  PaperCategory  $paperCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PaperCategory $paperCategory)
    {
        try {
            $this->paperCategoryService->deletePaperCategory($paperCategory);
            return redirect()->route('paper-categories.index')
                ->with('success', 'Paper Category deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting paper category: ' . $e->getMessage());
        }
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
        return view('paper_categories.index', compact('paperCategories', 'searchTerm'));
    }
}