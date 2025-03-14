<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleFormRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->middleware('permission:view-roles')->only(['index', 'show']);
        $this->middleware('permission:create-roles')->only(['create', 'store']);
        $this->middleware('permission:edit-roles')->only(['edit', 'update']);
        $this->middleware('permission:delete-roles')->only('destroy');
    }

    /**
     * Display a listing of roles with caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cacheKey = 'roles:list:' . ($request->page ?? 1);
        
        $roles = Cache::remember($cacheKey, now()->addHours(1), function() {
            return Role::with('permissions')->paginate(15);
        });
        
        if ($request->expectsJson()) {
            return RoleResource::collection($roles);
        }
        
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = Cache::remember('permissions:all', now()->addDay(), function() {
            return Permission::all(['id', 'name']);
        });
        
        // Group permissions by module for easier selection
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            // Extract module name from permission (e.g. "view-users" -> "users")
            $parts = explode('-', $permission->name);
            return end($parts);
        });
        
        return view('roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  RoleFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(RoleFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $role = $this->roleService->createRole($validatedData);
            
            // Clear relevant caches
            $this->clearRoleCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully!',
                    'data' => new RoleResource($role->load('permissions'))
                ], 201);
            }
            
            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create role', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating role: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role with its permissions.
     *
     * @param  Role  $role
     * @param  Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Role $role, Request $request)
    {
        $cacheKey = "role:{$role->id}:details";
        
        $role = Cache::remember($cacheKey, now()->addHour(), function() use ($role) {
            return $role->load(['permissions', 'users']);
        });
        
        if ($request->expectsJson()) {
            return (new RoleResource($role))
                ->additional([
                    'users_count' => $role->users->count()
                ]);
        }
        
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  Role  $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        
        $permissions = Cache::remember('permissions:all', now()->addDay(), function() {
            return Permission::all(['id', 'name']);
        });
        
        // Group permissions by module for easier selection
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return end($parts);
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param  RoleFormRequest  $request
     * @param  Role  $role
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(RoleFormRequest $request, Role $role)
    {
        $validatedData = $request->validated();
        
        try {
            // Prevent modification of 'admin' role for security
            if ($role->name === 'admin' && $validatedData['name'] !== 'admin') {
                throw new \Exception('The admin role name cannot be changed');
            }
            
            $this->roleService->updateRole($role, $validatedData);
            
            // Clear relevant caches
            $this->clearRoleCaches($role->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role updated successfully!',
                    'data' => new RoleResource($role->fresh()->load('permissions'))
                ]);
            }
            
            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update role', [
                'id' => $role->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating role: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage with safety checks.
     *
     * @param  Role  $role
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role, Request $request)
    {
        try {
            // Prevent deletion of 'admin' role for security
            if ($role->name === 'admin') {
                throw new \Exception('The admin role cannot be deleted');
            }
            
            // Check if role has users assigned
            if ($role->users()->count() > 0) {
                throw new \Exception('Cannot delete a role with users assigned to it. Reassign users first.');
            }
            
            $this->roleService->deleteRole($role);
            
            // Clear relevant caches
            $this->clearRoleCaches($role->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully!'
                ]);
            }
            
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete role', [
                'id' => $role->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting role: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting role: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear role related caches.
     *
     * @param int|null $roleId
     * @return void
     */
    protected function clearRoleCaches($roleId = null)
    {
        Cache::forget('roles:list');
        
        if ($roleId) {
            Cache::forget("role:{$roleId}:details");
        }
        
        // Clear user permissions cache
        Cache::forget('permissions:all');
    }
}
