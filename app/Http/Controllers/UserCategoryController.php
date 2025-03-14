<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCategoryFormRequest;
use App\Http\Resources\UserCategoryResource;
use App\Services\UserCategoryService;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserCategoryController extends Controller
{
    protected $userCategoryService;

    public function __construct(UserCategoryService $userCategoryService)
    {
        $this->userCategoryService = $userCategoryService;
        $this->middleware('permission:view-user-categories')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-user-categories')->only(['create', 'store']);
        $this->middleware('permission:edit-user-categories')->only(['edit', 'update']);
        $this->middleware('permission:delete-user-categories')->only('destroy');
    }

    /**
     * Display a listing of user categories with caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cacheKey = 'user_categories:list:' . ($request->page ?? 1);
        
        $userCategories = Cache::remember($cacheKey, now()->addMinutes(30), function() {
            return $this->userCategoryService->getPaginatedUserCategories();
        });
        
        // Get statistics
        $stats = Cache::remember('user_categories:stats', now()->addHours(2), function() {
            return [
                'total' => UserCategory::count(),
                'active_users_count' => UserCategory::withCount(['users' => function($query) {
                    $query->where('status', 'active');
                }])->get()->sum('users_count'),
                'empty_categories' => UserCategory::doesntHave('users')->count()
            ];
        });
        
        if ($request->expectsJson()) {
            return UserCategoryResource::collection($userCategories)
                ->additional(['stats' => $stats]);
        }
        
        return view('user_categories.index', compact('userCategories', 'stats'));
    }

    /**
     * Show the form for creating a new user category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('user_categories.create');
    }

    /**
     * Store a newly created user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(UserCategoryFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $userCategory = $this->userCategoryService->createUserCategory($validatedData);
            
            // Clear cache
            $this->clearUserCategoryCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Category created successfully!',
                    'data' => new UserCategoryResource($userCategory)
                ], 201);
            }
            
            return redirect()->route('user-categories.index')
                ->with('success', 'User Category created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create user category', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating user category: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating user category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user category with users.
     *
     * @param  UserCategory  $userCategory
     * @param  Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(UserCategory $userCategory, Request $request)
    {
        $cacheKey = "user_category:{$userCategory->id}:details";
        
        $userCategory = Cache::remember($cacheKey, now()->addHours(1), function() use ($userCategory) {
            return $userCategory->load(['users' => function($query) {
                $query->latest()->take(20);
            }]);
        });
        
        $stats = Cache::remember("user_category:{$userCategory->id}:stats", now()->addHours(1), function() use ($userCategory) {
            return [
                'total_users' => $userCategory->users()->count(),
                'active_users' => $userCategory->users()->where('status', 'active')->count(),
                'inactive_users' => $userCategory->users()->where('status', '!=', 'active')->count(),
                'newest_user' => $userCategory->users()->latest()->first()?->name ?? 'N/A',
                'oldest_user' => $userCategory->users()->oldest()->first()?->name ?? 'N/A'
            ];
        });
        
        if ($request->expectsJson()) {
            return (new UserCategoryResource($userCategory))
                ->additional(['stats' => $stats]);
        }
        
        return view('user_categories.show', compact('userCategory', 'stats'));
    }

    /**
     * Show the form for editing the specified user category.
     *
     * @param  UserCategory  $userCategory
     * @return \Illuminate\View\View
     */
    public function edit(UserCategory $userCategory)
    {
        return view('user_categories.edit', compact('userCategory'));
    }

    /**
     * Update the specified user category in storage.
     *
     * @param  UserCategoryFormRequest  $request
     * @param  UserCategory  $userCategory
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(UserCategoryFormRequest $request, UserCategory $userCategory)
    {
        $validatedData = $request->validated();
        
        try {
            $this->userCategoryService->updateUserCategory($userCategory, $validatedData);
            
            // Clear cache
            $this->clearUserCategoryCaches($userCategory->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Category updated successfully!',
                    'data' => new UserCategoryResource($userCategory->fresh())
                ]);
            }
            
            return redirect()->route('user-categories.index')
                ->with('success', 'User Category updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update user category', [
                'id' => $userCategory->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user category: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating user category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user category from storage with checks.
     *
     * @param  UserCategory  $userCategory
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(UserCategory $userCategory, Request $request)
    {
        try {
            // Check if category has users before deleting
            if ($userCategory->users()->count() > 0) {
                throw new \Exception('Cannot delete category that has users. Please reassign users first.');
            }
            
            $this->userCategoryService->deleteUserCategory($userCategory);
            
            // Clear cache
            $this->clearUserCategoryCaches($userCategory->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Category deleted successfully!'
                ]);
            }
            
            return redirect()->route('user-categories.index')
                ->with('success', 'User Category deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete user category', [
                'id' => $userCategory->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting user category: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting user category: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of user categories matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->validate([
            'search' => 'required|string|max:100'
        ])['search'];
        
        $userCategories = $this->userCategoryService->searchUserCategories($searchTerm);
        
        if ($request->expectsJson()) {
            return UserCategoryResource::collection($userCategories);
        }
        
        return view('user_categories.index', compact('userCategories', 'searchTerm'));
    }
    
    /**
     * Get users for a specific category.
     *
     * @param UserCategory $userCategory
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function users(UserCategory $userCategory, Request $request)
    {
        $status = $request->validate(['status' => 'nullable|in:active,inactive,banned'])['status'] ?? null;
        
        $cacheKey = "user_category:{$userCategory->id}:users:" . md5($status . ($request->page ?? 1));
        
        $users = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($userCategory, $status) {
            $query = $userCategory->users();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            return $query->paginate(15);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        }
        
        return view('user_categories.users', compact('userCategory', 'users', 'status'));
    }
    
    /**
     * Clear user category related caches.
     *
     * @param int|null $userCategoryId
     * @return void
     */
    protected function clearUserCategoryCaches($userCategoryId = null)
    {
        Cache::forget('user_categories:list');
        Cache::forget('user_categories:stats');
        
        if ($userCategoryId) {
            Cache::forget("user_category:{$userCategoryId}:details");
            Cache::forget("user_category:{$userCategoryId}:stats");
            
            // Clear users cache for this category with any status filter
            $statuses = [null, 'active', 'inactive', 'banned'];
            foreach ($statuses as $status) {
                Cache::forget("user_category:{$userCategoryId}:users:" . md5($status . '1'));
            }
        }
    }
}