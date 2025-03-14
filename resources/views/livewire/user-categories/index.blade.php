<?php

use App\Models\UserCategory;
use App\Services\UserCategoryService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';
    public bool $drawer = false;
    public bool $addEditModal = false;
    public bool $viewModal = false;
    public ?int $editingId = null;
    public ?int $viewingId = null;
    
    // UserCategory properties for create/edit
    public $name = '';
    public $description = '';
    public $is_default = false;
    public $permissions = [];
    
    protected $queryString = [
        'search' => ['except' => ''],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(UserCategoryService $userCategoryService) 
    {
        $this->userCategoryService = $userCategoryService;
    }
    
    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'sort_by', 'sort_dir']);
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    public function view($id): void
    {
        $this->viewingId = $id;
        $this->viewModal = true;
    }
    
    public function edit($id): void
    {
        $category = UserCategory::with('permissions')->find($id);
        if ($category) {
            $this->editingId = $id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->is_default = $category->is_default;
            $this->permissions = $category->permissions->pluck('id')->toArray();
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'is_default', 'permissions']);
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_default' => 'boolean',
            'permissions' => 'array'
        ]);
        
        try {
            if ($this->editingId) {
                $category = UserCategory::findOrFail($this->editingId);
                $this->userCategoryService->updateUserCategory($category, $data);
                $this->success('User category updated successfully!', position: 'toast-bottom');
            } else {
                $this->userCategoryService->createUserCategory($data);
                $this->success('User category created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'description', 'is_default', 'permissions']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $category = UserCategory::findOrFail($id);
            
            // Check if category is default
            if ($category->is_default) {
                throw new \Exception('Cannot delete default user category.');
            }
            
            // Check if category has users
            if ($category->users()->exists()) {
                throw new \Exception('Cannot delete category with assigned users. Please reassign users first.');
            }
            
            $this->userCategoryService->deleteUserCategory($category);
            $this->success('User category deleted successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function setAsDefault($id): void
    {
        try {
            $category = UserCategory::findOrFail($id);
            
            // Update all categories to non-default
            UserCategory::where('is_default', true)->update(['is_default' => false]);
            
            // Set selected category as default
            $category->update(['is_default' => true]);
            $this->success('Default category updated successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function print()
    {
        $this->dispatch('printTable');
    }
    
    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'description', 'label' => 'Description', 'sortable' => false],
            ['key' => 'users_count', 'label' => 'Users', 'sortable' => true],
            ['key' => 'is_default', 'label' => 'Default', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Created At', 'sortable' => true],
        ];
    }
    
    public function sortBy($column): void
    {
        if ($this->sort_by === $column) {
            $this->sort_dir = $this->sort_dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_dir = 'asc';
        }
        
        $this->sort_by = $column;
    }
    
    public function getUserCategoriesProperty()
    {
        // Using withCount to avoid N+1 problem
        return UserCategory::withCount('users')
            ->when($this->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(10);
    }
    
    public function getViewingUserCategoryProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return UserCategory::with([
                'permissions:id,name',
                'papers:id,name',
                'users:id,name,email' => function($query) {
                    $query->take(5);
                }
            ])
            ->withCount(['users', 'papers', 'permissions'])
            ->find($this->viewingId);
    }

    public function getPermissionsProperty()
    {
        // Cache permissions to improve performance
        return cache()->remember('category_permissions', now()->addDay(), function() {
            return \Spatie\Permission\Models\Permission::select(['id', 'name'])
                ->orderBy('name')
                ->get()
                ->groupBy(function($permission) {
                    // Group permissions by their prefix (e.g., "paper.", "question.")
                    $parts = explode('.', $permission->name);
                    return count($parts) > 1 ? $parts[0] : 'general';
                });
        });
    }

    public function with(): array
    {
        return [
            'userCategories' => $this->userCategories,
            'viewingUserCategory' => $this->viewingUserCategory,
            'permissions' => $this->permissions,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="User Categories" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search categories..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Create Category" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" sortable wire:loading.class="opacity-50">
            @foreach($userCategories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ Str::limit($category->description, 50) }}</td>
                    <td>{{ $category->users_count }}</td>
                    <td>
                        @if($category->is_default)
                            <x-badge value="Default" color="success" />
                        @else
                            <x-button size="xs" label="Set Default" 
                                    wire:click="setAsDefault({{ $category->id }})"
                                    wire:confirm="Are you sure you want to set this as the default category?"
                                    class="btn-outline btn-xs" />
                        @endif
                    </td>
                    <td>{{ $category->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="flex gap-1">
                            <x-button icon="o-eye" wire:click="view({{ $category->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                            <x-button icon="o-pencil" wire:click="edit({{ $category->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                            @unless($category->is_default)
                                <x-button icon="o-trash" wire:click="delete({{ $category->id }})" 
                                    wire:confirm="Are you sure you want to delete this category?" 
                                    spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        
        <div class="mt-4">
            {{ $userCategories->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter User Categories" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="name" label="Name" />
                        <x-select.option value="users_count" label="Users Count" />
                        <x-select.option value="is_default" label="Default Status" />
                        <x-select.option value="created_at" label="Created At" />
                    </x-select>
                    <x-select wire:model.live="sort_dir" class="w-1/3">
                        <x-select.option value="asc" label="Asc" />
                        <x-select.option value="desc" label="Desc" />
                    </x-select>
                </div>
            </div>
        </div>
        
        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Apply" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>

    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="User Category Details" size="lg">
        @if($viewingUserCategory)
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $viewingUserCategory->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        @if($viewingUserCategory->is_default)
                            <x-badge value="Default Category" color="success" />
                        @else
                            <x-badge value="Regular Category" color="info" />
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created</p>
                        <p class="font-medium">{{ $viewingUserCategory->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p>{{ $viewingUserCategory->description ?: 'No description provided' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Users</p>
                        <p class="font-medium">{{ $viewingUserCategory->users_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Papers</p>
                        <p class="font-medium">{{ $viewingUserCategory->papers_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Permissions</p>
                        <p class="font-medium">{{ $viewingUserCategory->permissions_count }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm text-gray-500">Users ({{ $viewingUserCategory->users_count }})</p>
                            <a href="{{ route('user-categories.users', $viewingUserCategory->id) }}" class="text-sm text-blue-500">View All</a>
                        </div>
                        @if($viewingUserCategory->users->count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach($viewingUserCategory->users as $user)
                                    <li class="py-2">
                                        <p class="font-medium">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No users assigned to this category.</p>
                        @endif
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm text-gray-500">Papers ({{ $viewingUserCategory->papers_count }})</p>
                            <a href="{{ route('user-categories.papers', $viewingUserCategory->id) }}" class="text-sm text-blue-500">View All</a>
                        </div>
                        @if($viewingUserCategory->papers->count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach($viewingUserCategory->papers as $paper)
                                    <li class="py-2">
                                        <p class="font-medium">{{ $paper->name }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No papers assigned to this category.</p>
                        @endif
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500 mb-2">Permissions</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($viewingUserCategory->permissions as $permission)
                            <x-badge value="{{ $permission->name }}" />
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        <x-slot:actions>
            <x-button label="Close" @click="$wire.viewModal = false" />
            <x-button label="Edit" wire:click="edit({{ $viewingId }})" @click="$wire.viewModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
    
    <!-- ADD/EDIT MODAL -->
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} User Category" size="xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="name" value="Category Name" />
                    <x-input id="name" wire:model="name" placeholder="Enter category name" required />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="flex items-center mt-8">
                    <x-checkbox id="is_default" wire:model="is_default" />
                    <x-label for="is_default" class="ml-2" value="Set as default category" />
                    @error('is_default') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div>
                <x-label for="description" value="Description" />
                <x-textarea id="description" wire:model="description" placeholder="Enter description" />
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="permissions" value="Permissions" />
                <p class="text-sm text-gray-500 mb-2">Select permissions that users in this category will have</p>
                
                <div class="space-y-4">
                    @foreach($permissions as $group => $groupPermissions)
                        <div>
                            <p class="font-medium capitalize mb-2">{{ $group }} Permissions</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                @foreach($groupPermissions as $permission)
                                    <label class="cursor-pointer flex items-start gap-2">
                                        <input type="checkbox" class="checkbox" 
                                            value="{{ $permission->id }}" 
                                            wire:model="permissions" />
                                        <span>{{ str_replace("$group.", "", $permission->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @error('permissions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </form>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.addEditModal = false" />
            <x-button label="{{ $editingId ? 'Update' : 'Create' }}" wire:click="save" spinner class="btn-primary" />
        </x-slot:actions>
    </x-modal>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('printTable', () => {
                const printContents = document.getElementById('printable-table').innerHTML;
                const originalContents = document.body.innerHTML;
                
                document.body.innerHTML = `
                    <div class="print-container">
                        <h1 class="text-center text-xl font-bold mb-4">User Categories List</h1>
                        ${printContents}
                    </div>
                `;
                
                window.print();
                document.body.innerHTML = originalContents;
                @this.dispatch('livewire:initialized');
            });
        });
    </script>
</div>
