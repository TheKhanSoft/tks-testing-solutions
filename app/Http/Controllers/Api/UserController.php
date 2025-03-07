<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserFormRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getPaginatedUsers();
        return response()->json(['data' => $users, 'message' => 'Users retrieved successfully'], 200);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  UserFormRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->userService->createUser($validatedData);

        return response()->json(['data' => $user, 'message' => 'User created successfully'], 201);
    }

    /**
     * Display the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user, 'message' => 'User retrieved successfully'], 200);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  UserFormRequest  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserFormRequest $request, User $user): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedUser = $this->userService->updateUser($user, $validatedData);

        return response()->json(['data' => $updatedUser, 'message' => 'User updated successfully'], 200);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}