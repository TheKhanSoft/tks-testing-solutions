<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\UserCategory; // Import UserCategory model
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->userService->getPaginatedUsers();
        return view('users.index', compact('users')); // Assuming you have a users.index view
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $userCategories = UserCategory::all(); // Fetch user categories for dropdown
        return view('users.create', compact('userCategories')); // Assuming you have a users.create view
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  UserFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserFormRequest $request)
    {
        $validatedData = $request->validated();
        $this->userService->createUser($validatedData);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('users.show', compact('user')); // Assuming you have a users.show view
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $userCategories = UserCategory::all(); // Fetch user categories for dropdown
        return view('users.edit', compact('user', 'userCategories')); // Assuming you have a users.edit view
    }

    /**
     * Update the specified user in storage.
     *
     * @param  UserFormRequest  $request
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserFormRequest $request, User $user)
    {
        $validatedData = $request->validated();
        $this->userService->updateUser($user, $validatedData);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    /**
     * Display a listing of users matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        $users = $this->userService->searchUsers($searchTerm);
        return view('users.index', compact('users', 'searchTerm')); // Reusing index view, passing searchTerm
    }
}