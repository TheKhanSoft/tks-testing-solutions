<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\UserCategory;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    // Filter properties
    public string $search = '';
    public string $status = '';
    public string $role = '';
    public string $userCategoryId = '';
    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';
    public int $perPage = 10;
    
    // Export related properties
    public string $exportFormat = 'csv';
    
    // UI state properties
    public bool $showFilters = false;
    public bool $showDeleteModal = false;
    public bool $showExportModal = false;
    public $userToDelete = null;
    
    // For mass actions
    public array $selected = [];
    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'role' => ['except' => ''],
        'userCategoryId' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDir' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refreshUsers' => '$refresh',
    ];

    /**
     * Reset pagination when filters change
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedRole()
    {
        $this->resetPage();
    }

    public function updatedUserCategoryId()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Sort by a given column
     */
    public function sortBy(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    /**
     * Reset all filters
     */
    public function resetFilters()
    {
        $this->reset(['search', 'status', 'role', 'userCategoryId']);
        $this->resetPage();
    }

    /**
     * Toggle select all users
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getFilteredUsers()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Confirm delete action
     */
    public function confirmDelete($userId)
    {
        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a user
     */
    public function deleteUser()
    {
        try {
            $user = User::findOrFail($this->userToDelete);
            
            // Prevent deleting your own account
            if ($user->id === Auth::id()) {
                session()->flash('error', 'You cannot delete your own account.');
                $this->showDeleteModal = false;
                return;
            }
            
            // Check if user is the only admin
            if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
                session()->flash('error', 'Cannot delete the only admin user.');
                $this->showDeleteModal = false;
                return;
            }
            
            app(UserService::class)->deleteUser($user);
            
            session()->flash('success', 'User deleted successfully.');
            $this->showDeleteModal = false;
            $this->userToDelete = null;
            
            // Clear relevant caches
            $this->clearUserCaches($user->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Export users based on current filters
     */
    public function exportUsers()
    {
        try {
            $filters = $this->getFiltersArray();
            $exportPath = app(UserService::class)->exportUsers($this->exportFormat, $filters);
            
            $this->showExportModal = false;
            $this->dispatch('userExportReady', path: $exportPath);
            
            session()->flash('success', 'Users exported successfully. Download starting...');
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting users: ' . $e->getMessage());
        }
    }

    /**
     * Mass delete selected users
     */
    public function deleteSelected()
    {
        try {
            $userService = app(UserService::class);
            $currentUserId = Auth::id();
            $adminRole = Role::findByName('admin');
            $adminCount = User::role('admin')->count();
            
            // Get how many of the selected users are admins
            $selectedAdmins = User::whereIn('id', $this->selected)
                ->role('admin')
                ->count();
            
            // Check if deleting all selected admins would leave no admins
            if ($selectedAdmins >= $adminCount) {
                session()->flash('error', 'Cannot delete all admin users.');
                return;
            }
            
            foreach ($this->selected as $userId) {
                // Skip current user
                if ((int)$userId === $currentUserId) {
                    continue;
                }
                
                $user = User::find($userId);
                if ($user) {
                    $userService->deleteUser($user);
                    $this->clearUserCaches($userId);
                }
            }
            
            session()->flash('success', count($this->selected) . ' users deleted successfully.');
            $this->selected = [];
            $this->selectAll = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting selected users: ' . $e->getMessage());
        }
    }

    /**
     * Get array of current filters for queries
     */
    protected function getFiltersArray(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status ?: null,
            'role' => $this->role ?: null,
            'user_category_id' => $this->userCategoryId ?: null,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
        ];
    }

    /**
     * Get filtered users query for select all functionality
     */
    protected function getFilteredUsers()
    {
        $filters = $this->getFiltersArray();
        
        $query = User::query();
        
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        if ($filters['user_category_id']) {
            $query->where('user_category_id', $filters['user_category_id']);
        }
        
        if ($filters['role']) {
            $query->role($filters['role']);
        }
        
        if ($filters['search']) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }
        
        return $query;
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

    #[Layout('layouts.app')]
    #[Title('Manage Users')]
    public function render()
    {
        $userService = app(UserService::class);
        
        // Get users with pagination and eager loading
        $users = $userService->getPaginatedUsers(
            $this->getFiltersArray(), 
            $this->perPage,
            ['*'],
            ['userCategory:id,name', 'roles:id,name']
        );
        
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
        
        $roles = Cache::remember('roles:dropdown', now()->addDay(), function() {
            return Role::all(['id', 'name']);
        });
        
        $userCategories = Cache::remember('user_categories:dropdown', now()->addHours(2), function() {
            return UserCategory::all(['id', 'name']);
        });

        return view('livewire.users.index', [
            'users' => $users,
            'stats' => $stats,
            'roles' => $roles,
            'userCategories' => $userCategories,
        ]);
    }
}
