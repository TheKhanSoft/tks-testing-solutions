<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class PermissionService extends BaseService
{
    /**
     * PermissionService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Permission::class;
    }
    
    /**
     * Get all permissions.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated permissions.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Permission>
     */
    public function getPaginatedPermissions(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get a permission by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Permission|null
     */
    public function getPermissionById(int $id, array $columns = ['*'], array $relations = []): ?Permission
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a permission by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Permission
     */
    public function getPermissionByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Permission
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new permission.
     *
     * @param array $data
     * @return Permission
     */
    public function createPermission(array $data): Permission
    {
        return $this->create($data);
    }

    /**
     * Update an existing permission.
     *
     * @param Permission $permission
     * @param array $data
     * @return Permission
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        return $this->update($permission, $data);
    }

    /**
     * Delete a permission.
     *
     * @param Permission $permission
     * @return bool|null
     */
    public function deletePermission(Permission $permission): ?bool
    {
        return $this->delete($permission);
    }

    /**
     * Get a permission by name.
     *
     * @param string $name
     * @param string|null $guardName
     * @param array $columns
     * @param array $relations
     * @return Permission|null
     */
    public function getPermissionByName(string $name, ?string $guardName = null, array $columns = ['*'], array $relations = []): ?Permission
    {
        $query = Permission::where('name', $name);
        
        if ($guardName) {
            $query->where('guard_name', $guardName);
        }
        
        return $query->with($relations)->first($columns);
    }

    /**
     * Get permissions with roles.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $roleRelations
     * @return Paginator<Permission>|Collection<int, Permission>
     */
    public function getPermissionsWithRoles(int $perPage = 10, array $columns = ['*'], array $roleRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('roles', $roleRelations, $perPage, $columns);
    }

    /**
     * Get permissions by role.
     *
     * @param string|int $role Role ID or name
     * @param array $columns
     * @return Collection<int, Permission>
     */
    public function getPermissionsByRole(string|int $role, array $columns = ['*']): Collection
    {
        if (is_numeric($role)) {
            return Permission::whereHas('roles', function($query) use ($role) {
                $query->where('id', $role);
            })->get($columns);
        } else {
            return Permission::whereHas('roles', function($query) use ($role) {
                $query->where('name', $role);
            })->get($columns);
        }
    }

    /**
     * Create multiple permissions at once.
     *
     * @param array $permissions
     * @param string|null $guardName
     * @return Collection<int, Permission>
     */
    public function createMultiplePermissions(array $permissions, ?string $guardName = null): Collection
    {
        $result = collect();
        
        foreach ($permissions as $permission) {
            $data = [
                'name' => $permission,
            ];
            
            if ($guardName) {
                $data['guard_name'] = $guardName;
            }
            
            $result->push($this->createPermission($data));
        }
        
        return $result;
    }
    
    /**
     * Generate CRUD permissions for a module.
     *
     * @param string $module
     * @param string|null $guardName
     * @return Collection<int, Permission>
     */
    public function generateCrudPermissions(string $module, ?string $guardName = null): Collection
    {
        $actions = ['view', 'create', 'update', 'delete'];
        $permissions = [];
        
        foreach ($actions as $action) {
            $permissions[] = "{$action} {$module}";
        }
        
        return $this->createMultiplePermissions($permissions, $guardName);
    }
}
