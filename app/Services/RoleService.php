<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class RoleService extends BaseService
{
    /**
     * RoleService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Role::class;
    }
    
    /**
     * Get all roles.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Role>
     */
    public function getAllRoles(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated roles.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Role>
     */
    public function getPaginatedRoles(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get a role by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Role|null
     */
    public function getRoleById(int $id, array $columns = ['*'], array $relations = []): ?Role
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a role by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Role
     */
    public function getRoleByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Role
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new role.
     *
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role
    {
        return $this->create($data);
    }

    /**
     * Update an existing role.
     *
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role
    {
        return $this->update($role, $data);
    }

    /**
     * Delete a role.
     *
     * @param Role $role
     * @return bool|null
     */
    public function deleteRole(Role $role): ?bool
    {
        return $this->delete($role);
    }

    /**
     * Get a role by name.
     *
     * @param string $name
     * @param string|null $guardName
     * @param array $columns
     * @param array $relations
     * @return Role|null
     */
    public function getRoleByName(string $name, ?string $guardName = null, array $columns = ['*'], array $relations = []): ?Role
    {
        $query = Role::where('name', $name);
        
        if ($guardName) {
            $query->where('guard_name', $guardName);
        }
        
        return $query->with($relations)->first($columns);
    }

    /**
     * Get roles with users count.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Role>|Collection<int, Role>
     */
    public function getRolesWithUserCount(?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Role::withCount('users')->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get roles with permissions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $permissionRelations
     * @return Paginator<Role>|Collection<int, Role>
     */
    public function getRolesWithPermissions(int $perPage = 10, array $columns = ['*'], array $permissionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('permissions', $permissionRelations, $perPage, $columns);
    }

    /**
     * Add permissions to a role.
     *
     * @param Role $role
     * @param array|string $permissions
     * @return void
     */
    public function givePermissionsToRole(Role $role, array|string $permissions): void
    {
        $role->givePermissionTo($permissions);
    }

    /**
     * Remove permissions from a role.
     *
     * @param Role $role
     * @param array|string $permissions
     * @return void
     */
    public function revokePermissionsFromRole(Role $role, array|string $permissions): void
    {
        $role->revokePermissionTo($permissions);
    }

    /**
     * Sync permissions for a role.
     *
     * @param Role $role
     * @param array $permissions
     * @return void
     */
    public function syncPermissionsForRole(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
