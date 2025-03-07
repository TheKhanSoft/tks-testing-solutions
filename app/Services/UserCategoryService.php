<?php

namespace App\Services;

use App\Models\UserCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class UserCategoryService
{
    /**
     * Get all user categories.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, UserCategory>
     */
    public function getAllUserCategories(array $columns = ['*'], array $relations = []): Collection
    {
        return UserCategory::with($relations)->get($columns);
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
        return UserCategory::with($relations)->paginate($perPage, $columns);
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
        return UserCategory::with($relations)->find($id, $columns);
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
        return UserCategory::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new user category.
     *
     * @param array $data
     * @return UserCategory
     */
    public function createUserCategory(array $data): UserCategory
    {
        return UserCategory::create($data);
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
        $userCategory->update($data);
        return $userCategory;
    }

    /**
     * Delete a user category.
     *
     * @param UserCategory $userCategory
     * @return bool|null
     */
    public function deleteUserCategory(UserCategory $userCategory): ?bool
    {
        return $userCategory->delete();
    }

    /**
     * Restore a soft-deleted user category.
     *
     * @param int $id
     * @return bool
     */
    public function restoreUserCategory(int $id): bool
    {
        return UserCategory::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a user category permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteUserCategory(int $id): ?bool
    {
        return UserCategory::withTrashed()->findOrFail($id)->forceDelete();
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
        $query = UserCategory::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get user categories with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations Relations for papers if eager loading is needed for them as well
     * @return Paginator<UserCategory>|Collection<int, UserCategory>
     */
    public function getUserCategoriesWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        $query = UserCategory::with(['papers' => function ($query) use ($paperRelations) {
            if (!empty($paperRelations)) {
                $query->with($paperRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get user categories with users.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userRelations Relations for users if eager loading is needed for them as well
     * @return Paginator<UserCategory>|Collection<int, UserCategory>
     */
    public function getUserCategoriesWithUsers(int $perPage = 10, array $columns = ['*'], array $userRelations = []): Paginator|Collection
    {
        $query = UserCategory::with(['users' => function ($query) use ($userRelations) {
            if (!empty($userRelations)) {
                $query->with($userRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Add a paper to a user category.
     *
     * @param UserCategory $userCategory
     * @param int $paperId
     * @return void
     */
    public function addPaperToUserCategory(UserCategory $userCategory, int $paperId): void
    {
        $userCategory->papers()->attach($paperId);
    }

    /**
     * Remove a paper from a user category.
     *
     * @param UserCategory $userCategory
     * @param int $paperId
     * @return void
     */
    public function removePaperFromUserCategory(UserCategory $userCategory, int $paperId): void
    {
        $userCategory->papers()->detach($paperId);
    }
}