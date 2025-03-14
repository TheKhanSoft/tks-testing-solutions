<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class UserService extends BaseService
{
    /**
     * UserService constructor.
     */
    public function __construct()
    {
        $this->modelClass = User::class;
    }
    
    /**
     * Get all users.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, User>
     */
    public function getAllUsers(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    

    /**
     * Get paginated users with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10, array $columns = ['*'], array $relations = [])
    {
        $query = User::query()->with($relations);
        
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['user_category_id']) && $filters['user_category_id']) {
            $query->where('user_category_id', $filters['user_category_id']);
        }
        
        if (isset($filters['role']) && $filters['role']) {
            $query->role($filters['role']);
        }
        
        if (isset($filters['search']) && $filters['search']) {
            $query->where(function(Builder $q) use ($filters) {
                $search = '%' . $filters['search'] . '%';
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }
        
        if (isset($filters['sort_by']) && $filters['sort_by']) {
            $direction = $filters['sort_dir'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->latest();
        }
        
        return $query->paginate($perPage, $columns);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']); // Hash password before creating
        }
        
        return $this->create($data);
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
        
        return $this->update($user, $data);
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return bool|null
     */
    public function deleteUser(User $user): ?bool
    {
        return $this->delete($user);
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id
     * @return bool
     */
    public function restoreUser(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a user permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteUser(int $id): ?bool
    {
        return $this->forceDelete($id);
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
        return $this->search($searchTerm, $perPage, $columns, $relations);
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
     * @param array $userCategoryRelations
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersWithUserCategories(int $perPage = 10, array $columns = ['*'], array $userCategoryRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('userCategory', $userCategoryRelations, $perPage, $columns);
    }

    /**
     * Get users with test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersWithTestAttempts(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('testAttempts', $testAttemptRelations, $perPage, $columns);
    }
    
    /**
     * Get a user by email.
     *
     * @param string $email
     * @param array $columns
     * @param array $relations
     * @return User|null
     */
    public function getUserByEmail(string $email, array $columns = ['*'], array $relations = []): ?User
    {
        return User::where('email', $email)->with($relations)->first($columns);
    }
    
    /**
     * Get users by role.
     *
     * @param string $role
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<User>|Collection<int, User>
     */
    public function getUsersByRole(string $role, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = User::role($role)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
    
    /**
     * Assign a role to a user.
     *
     * @param User $user
     * @param string|array $roles
     * @return void
     */
    public function assignRole(User $user, string|array $roles): void
    {
        $user->assignRole($roles);
    }
    
    /**
     * Remove a role from a user.
     *
     * @param User $user
     * @param string|array $roles
     * @return void
     */
    public function removeRole(User $user, string|array $roles): void
    {
        $user->removeRole($roles);
    }

    /**
     * Export users based on filters.
     *
     * @param string $format
     * @param array $filters
     * @return string Path to the exported file
     */
    public function exportUsers(string $format, array $filters = []): string
    {
        // Get users to export based on filters
        $users = $this->getUsersForExport($filters);
        
        $fileName = 'users_export_' . now()->format('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'csv':
            case 'xlsx':
                return $this->exportToSpreadsheet($users, $format, $fileName);
            
            case 'pdf':
                return $this->exportToPdf($users, $fileName);
            
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }
    
    /**
     * Get users for export.
     *
     * @param array $filters
     * @return Collection<int, User>
     */
    protected function getUsersForExport(array $filters): Collection
    {
        $query = User::query()
            ->with(['userCategory:id,name', 'roles:id,name']);
        
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['user_category_id']) && $filters['user_category_id']) {
            $query->where('user_category_id', $filters['user_category_id']);
        }
        
        if (isset($filters['role']) && $filters['role']) {
            $query->role($filters['role']);
        }
        
        if (isset($filters['search']) && $filters['search']) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }
        
        if (isset($filters['sort_by']) && $filters['sort_by']) {
            $direction = $filters['sort_dir'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->latest();
        }
        
        return $query->get([
            'id', 'name', 'email', 'status', 'email_verified_at',
            'user_category_id', 'created_at', 'updated_at'
        ]);
    }
    
    /**
     * Export users to spreadsheet (CSV or XLSX).
     *
     * @param Collection $users
     * @param string $format
     * @param string $fileName
     * @return string Path to exported file
     */
    protected function exportToSpreadsheet(Collection $users, string $format, string $fileName): string
    {
        $filePath = "exports/{$fileName}.{$format}";
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        $writer = SimpleExcelWriter::create($fullPath);
        
        $exportData = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Status' => $user->status,
                'User Category' => $user->userCategory?->name ?? 'None',
                'Roles' => $user->roles->pluck('name')->implode(', '),
                'Email Verified' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'No',
                'Created At' => $user->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $user->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        $writer->addRows($exportData);
        
        return Storage::url($filePath);
    }
    
    /**
     * Export users to PDF.
     *
     * @param Collection $users
     * @param string $fileName
     * @return string Path to exported file
     */
    protected function exportToPdf(Collection $users, string $fileName): string
    {
        $filePath = "exports/{$fileName}.pdf";
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        $pdf = PDF::loadView('exports.users', [
            'users' => $users,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ]);
        
        $pdf->save($fullPath);
        
        return Storage::url($filePath);
    }

    /**
     * Get user activities.
     *
     * @param User $user
     * @return array
     */
    public function getUserActivities(User $user): array
    {
        // This is a stub method - actual implementation would depend on your audit logging system
        // For example, if you're using Laravel Auditing or Spatie Activitylog
        return []; // Return empty array as placeholder
    }
}