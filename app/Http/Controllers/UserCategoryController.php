<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCategoryFormRequest;
use App\Services\UserCategoryService;
use App\Models\UserCategory;
use Illuminate\Http\Request;

class UserCategoryController extends Controller
{
    protected $userCategoryService;

    public function __construct(UserCategoryService $userCategoryService)
    {
        $this->userCategoryService = $userCategoryService;
    }

    /**
     * Display a listing of user categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userCategories = $this->userCategoryService->getPaginatedUserCategories();
        return view('user_categories.index', compact('userCategories')); // Assuming you have a user_categories.index view
    }

    /**
     * Show the form for creating a new user category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('user_categories.create'); // Assuming you have a user_categories.create view
    }

    /**
     * Store a newly created user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserCategoryFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->userCategoryService->createUserCategory($validatedData);

        return redirect()->route('user-categories.index')->with('success', 'User Category created successfully!');
    }

    /**
     * Display the specified user category.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\View\View
     */
    public function show(UserCategory $userCategory)
    {
        return view('user_categories.show', compact('userCategory')); // Assuming you have a user_categories.show view
    }

    /**
     * Show the form for editing the specified user category.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\View\View
     */
    public function edit(UserCategory $userCategory)
    {
        return view('user_categories.edit', compact('userCategory')); // Assuming you have a user_categories.edit view
    }

    /**
     * Update the specified user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserCategoryFormRequest $request, UserCategory $userCategory)
    {
        $validatedData = $request->validated();
        $this->userCategoryService->updateUserCategory($userCategory, $validatedData);

        return redirect()->route('user-categories.index')->with('success', 'User Category updated successfully!');
    }

    /**
     * Remove the specified user category from storage.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(UserCategory $userCategory)
    {
        $this->userCategoryService->deleteUserCategory($userCategory);

        return redirect()->route('user-categories.index')->with('success', 'User Category deleted successfully!');
    }

    /**
     * Display a listing of user categories matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $userCategories = $this->userCategoryService->searchUserCategories($searchTerm);
        return view('user_categories.index', compact('userCategories', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}