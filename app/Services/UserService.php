<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get all users.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, User>
     */
    public function getAllUsers(array $columns = ['*'], array $relations = []): Collection
    {
        return User::with($relations)->get($columns);
    }

    /**
     * Get paginated users.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>
     */
    public function getPaginatedUsers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return User::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return User|null
     */
    public function getUserById(int $id, array $columns = ['*'], array $relations = []): ?User
    {
        return User::with($relations)->find($id, $columns);
    }

    /**
     * Get a user by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return User
     */
    public function getUserByIdOrFail(int $id, array $columns = ['*'], array $relations = []): User
    {
        return User::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']); // Hash password before creating
        return User::create($data);
    }

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']); // Hash password if updated
        }
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return bool|null
     */
    public function deleteUser(User $user): ?bool
    {
        return $user->delete();
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id
     * @return bool
     */
    public function restoreUser(int $id): bool
    {
        return User::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a user permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteUser(int $id): ?bool
    {
        return User::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search users by name or email.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>|Collection<int, User>
     */
    public function searchUsers(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = User::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get active users.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>|Collection<int, User>
     */
    public function getActiveUsers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = User::active()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get users by user category.
     *
     * @param int $userCategoryId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersByUserCategory(int $userCategoryId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = User::where('user_category_id', $userCategoryId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get users with user categories.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userCategoryRelations Relations for user categories if eager loading is needed for them as well
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersWithUserCategories(int $perPage = 10, array $columns = ['*'], array $userCategoryRelations = []): Paginator|Collection
    {
        $query = User::with(['userCategory' => function ($query) use ($userCategoryRelations) {
            if (!empty($userCategoryRelations)) {
                $query->with($userCategoryRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get users with test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations Relations for test attempts if eager loading is needed for them as well
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersWithTestAttempts(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        $query = User::with(['testAttempts' => function ($query) use ($testAttemptRelations) {
            if (!empty($testAttemptRelations)) {
                $query->with($testAttemptRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }
}