<?php

namespace App\Services;

use App\Models\UserCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class UserCategoryService extends BaseService
{
    /**
     * UserCategoryService constructor.
     */
    public function __construct()
    {
        $this->modelClass = UserCategory::class;
    }
    
    /**
     * Get all user categories.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, UserCategory>
     */
    public function getAllUserCategories(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated user categories.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<UserCategory>
     */
    public function getPaginatedUserCategories(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get a user category by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return UserCategory|null
     */
    public function getUserCategoryById(int $id, array $columns = ['*'], array $relations = []): ?UserCategory
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a user category by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return UserCategory
     */
    public function getUserCategoryByIdOrFail(int $id, array $columns = ['*'], array $relations = []): UserCategory
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new user category.
     *
     * @param array $data
     * @return UserCategory
     */
    public function createUserCategory(array $data): UserCategory
    {
        return $this->create($data);
    }

    /**
     * Update an existing user category.
     *
     * @param UserCategory $userCategory
     * @param array $data
     * @return UserCategory
     */
    public function updateUserCategory(UserCategory $userCategory, array $data): UserCategory
    {
        return $this->update($userCategory, $data);
    }

    /**
     * Delete a user category.
     *
     * @param UserCategory $userCategory
     * @return bool|null
     */
    public function deleteUserCategory(UserCategory $userCategory): ?bool
    {
        return $this->delete($userCategory);
    }

    /**
     * Restore a soft-deleted user category.
     *
     * @param int $id
     * @return bool
     */
    public function restoreUserCategory(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a user category permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteUserCategory(int $id): ?bool
    {
        return $this->forceDelete($id);
    }

    /**
     * Search user categories by name or description.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<UserCategory>|Collection<int, UserCategory>
     */
    public function searchUserCategories(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get user categories with users.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userRelations
     * @return Paginator<UserCategory>|Collection<int, UserCategory>
     */
    public function getUserCategoriesWithUsers(int $perPage = 10, array $columns = ['*'], array $userRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('users', $userRelations, $perPage, $columns);
    }

    /**
     * Get user categories with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations
     * @return Paginator<UserCategory>|Collection<int, UserCategory>
     */
    public function getUserCategoriesWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('papers', $paperRelations, $perPage, $columns);
    }
}
