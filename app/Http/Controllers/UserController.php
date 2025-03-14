<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('permission:view-users')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-users')->only(['create', 'store']);
        $this->middleware('permission:edit-users')->only(['edit', 'update', 'updatePassword']);
        $this->middleware('permission:delete-users')->only('destroy');
        $this->middleware('permission:manage-user-roles')->only(['assignRoles', 'updateRoles']);
    }

    /**
     * Display a listing of users with filtering and caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Extract filters from request
        $filters = $request->validate([
            'status' => 'nullable|in:active,inactive,banned',
            'role' => 'nullable|string|exists:roles,name',
            'user_category_id' => 'nullable|exists:user_categories,id',
            'search' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:name,email,created_at',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'users:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $users = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($filters) {
            return $this->userService->getPaginatedUsers($filters);
        });
        
        // Get statistics for dashboard from cache or compute
        $stats = Cache::remember('users:stats', now()->addHours(1), function() {
            return [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
                'banned' => User::where('status', 'banned')->count(),
                'admins' => User::role('admin')->count()
            ];
        });
        
        if ($request->expectsJson()) {
            return UserResource::collection($users)
                ->additional(['stats' => $stats]);
        }
        
        $roles = Cache::remember('roles:dropdown', now()->addDay(), function() {
            return Role::all(['id', 'name']);
        });
        
        $userCategories = Cache::remember('user_categories:dropdown', now()->addHours(2), function() {
            return UserCategory::all(['id', 'name']);
        });
        
        return view('users.index', compact('users', 'filters', 'stats', 'roles', 'userCategories'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Cache::remember('roles:dropdown', now()->addDay(), function() {
            return Role::all(['id', 'name']);
        });
        
        $userCategories = Cache::remember('user_categories:dropdown', now()->addHours(2), function() {
            return UserCategory::all(['id', 'name']);
        });
        
        return view('users.create', compact('roles', 'userCategories'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  UserFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(UserFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $user = $this->userService->createUser($validatedData);
            
            // Clear relevant caches
            $this->clearUserCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully!',
                    'data' => new UserResource($user)
                ], 201);
            }
            
            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'data' => array_except($validatedData, ['password']),
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating user: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user with relationships.
     *
     * @param  User  $user
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(User $user, Request $request)
    {
        $cacheKey = "user:{$user->id}:details";
        
        $user = Cache::remember($cacheKey, now()->addHours(1), function() use ($user) {
            return $user->load(['roles', 'userCategory']);
        });
        
        // Get user activity from audit logs
        $activities = Cache::remember("user:{$user->id}:activities", now()->addMinutes(15), function() use ($user) {
            return $this->userService->getUserActivities($user);
        });
        
        if ($request->expectsJson()) {
            return (new UserResource($user))
                ->additional(['activities' => $activities]);
        }
        
        return view('users.show', compact('user', 'activities'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Cache::remember('roles:dropdown', now()->addDay(), function() {
            return Role::all(['id', 'name']);
        });
        
        $userCategories = Cache::remember('user_categories:dropdown', now()->addHours(2), function() {
            return UserCategory::all(['id', 'name']);
        });
        
        return view('users.edit', compact('user', 'roles', 'userCategories'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  UserFormRequest  $request
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(UserFormRequest $request, User $user)
    {
        $validatedData = $request->validated();
        
        try {
            $this->userService->updateUser($user, $validatedData);
            
            // Clear relevant caches
            $this->clearUserCaches($user->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'data' => new UserResource($user->fresh(['roles', 'userCategory']))
                ]);
            }
            
            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'id' => $user->id,
                'data' => array_except($validatedData, ['password']),
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage with proper checks.
     *
     * @param  User  $user
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(User $user, Request $request)
    {
        try {
            // Prevent deleting your own account
            if ($user->id === auth()->id()) {
                throw new \Exception('You cannot delete your own account.');
            }
            
            // Check if user is the only admin
            if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
                throw new \Exception('Cannot delete the only admin user.');
            }
            
            $this->userService->deleteUser($user);
            
            // Clear relevant caches
            $this->clearUserCaches($user->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully!'
                ]);
            }
            
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting user: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Update user password.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);
        
        try {
            $user->password = Hash::make($validatedData['password']);
            $user->save();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully!'
                ]);
            }
            
            return redirect()->route('users.show', $user)
                ->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update user password', [
                'id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating password: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating password: ' . $e->getMessage());
        }
    }

    /**
     * Show form to assign roles to user.
     *
     * @param  User  $user
     * @return \Illuminate\View\View
     */
    public function assignRoles(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('users.assign_roles', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user roles.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updateRoles(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        try {
            // Special protection for admin role to prevent accidentally removing all admins
            if ($user->hasRole('admin') && !in_array(Role::findByName('admin')->id, $validatedData['roles'])) {
                if (User::role('admin')->count() <= 1) {
                    throw new \Exception('Cannot remove admin role from the only admin user.');
                }
            }
            
            $roles = Role::whereIn('id', $validatedData['roles'])->get();
            $user->syncRoles($roles);
            
            // Clear relevant caches
            $this->clearUserCaches($user->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User roles updated successfully!',
                    'data' => new UserResource($user->fresh(['roles']))
                ]);
            }
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User roles updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update user roles', [
                'id' => $user->id,
                'roles' => $validatedData['roles'],
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user roles: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating user roles: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear user related caches.
     *
     * @param int|null $userId
     * @return void
     */
    protected function clearUserCaches($userId = null)
    {
        Cache::forget('users:list');
        Cache::forget('users:stats');
        
        if ($userId) {
            Cache::forget("user:{$userId}:details");
            Cache::forget("user:{$userId}:activities");
            Cache::forget("user:{$userId}:permissions");
        }
    }
}