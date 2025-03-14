<?php

use App\Models\Department;
use App\Services\DepartmentService;
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
    
    // Department properties for create/edit
    public $name = '';
    public $code = '';
    public $description = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(DepartmentService $departmentService) 
    {
        $this->departmentService = $departmentService;
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
        $department = Department::find($id);
        if ($department) {
            $this->editingId = $id;
            $this->name = $department->name;
            $this->code = $department->code;
            $this->description = $department->description;
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'code', 'description']);
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:2000'
        ]);
        
        try {
            if ($this->editingId) {
                $department = Department::findOrFail($this->editingId);
                $this->departmentService->updateDepartment($department, $data);
                $this->success('Department updated successfully!', position: 'toast-bottom');
            } else {
                $this->departmentService->createDepartment($data);
                $this->success('Department created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'code', 'description']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $department = Department::findOrFail($id);
            
            // Check if department has related records
            if ($department->subjects()->exists() || $department->facultyMembers()->exists()) {
                throw new \Exception('Cannot delete department with related subjects or faculty members');
            }
            
            $this->departmentService->deleteDepartment($department);
            $this->success('Department deleted successfully!', position: 'toast-bottom');
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
            ['key' => 'code', 'label' => 'Code', 'sortable' => true],
            ['key' => 'faculty_members_count', 'label' => 'Faculty', 'sortable' => true],
            ['key' => 'subjects_count', 'label' => 'Subjects', 'sortable' => true],
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
    
    public function getDepartmentsWithCountsProperty()
    {
        // Using withCount to avoid N+1 problem when displaying counts
        return Department::withCount(['facultyMembers', 'subjects'])
            ->when($this->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(10);
    }
    
    public function getViewingDepartmentProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return Department::withCount(['facultyMembers', 'subjects'])
            ->with([
                'facultyMembers' => function($query) {
                    $query->select(['id', 'name', 'email', 'department_id'])->take(5);
                },
                'subjects' => function($query) {
                    $query->select(['subjects.id', 'subjects.name', 'subjects.code'])->take(5);
                }
            ])
            ->find($this->viewingId);
    }

    public function with(): array
    {
        return [
            'departments' => $this->departmentsWithCounts,
            'viewingDepartment' => $this->viewingDepartment,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Departments" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search departments..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Create Department" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" sortable wire:loading.class="opacity-50">
            @foreach($departments as $department)
                <tr>
                    <td>{{ $department->id }}</td>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->code }}</td>
                    <td>{{ $department->faculty_members_count }}</td>
                    <td>{{ $department->subjects_count }}</td>
                    <td>{{ $department->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="flex gap-1">
                            <x-button icon="o-eye" wire:click="view({{ $department->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                            <x-button icon="o-pencil" wire:click="edit({{ $department->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                            <x-button icon="o-trash" wire:click="delete({{ $department->id }})" wire:confirm="Are you sure you want to delete this department?" spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        
        <div class="mt-4">
            {{ $departments->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Departments" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="name" label="Name" />
                        <x-select.option value="code" label="Code" />
                        <x-select.option value="faculty_members_count" label="Faculty Count" />
                        <x-select.option value="subjects_count" label="Subjects Count" />
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
    <x-modal wire:model="viewModal" title="Department Details" size="lg">
        @if($viewingDepartment)
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $viewingDepartment->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Code</p>
                        <p class="font-medium">{{ $viewingDepartment->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created</p>
                        <p class="font-medium">{{ $viewingDepartment->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p>{{ $viewingDepartment->description ?: 'No description available' }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm text-gray-500">Faculty Members ({{ $viewingDepartment->faculty_members_count }})</p>
                            <a href="{{ route('departments.faculty-members', $viewingDepartment->id) }}" class="text-sm text-blue-500">View All</a>
                        </div>
                        @if($viewingDepartment->facultyMembers->count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach($viewingDepartment->facultyMembers as $faculty)
                                    <li class="py-2">
                                        <p class="font-medium">{{ $faculty->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $faculty->email }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No faculty members assigned yet.</p>
                        @endif
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm text-gray-500">Subjects ({{ $viewingDepartment->subjects_count }})</p>
                            <a href="{{ route('departments.subjects', $viewingDepartment->id) }}" class="text-sm text-blue-500">View All</a>
                        </div>
                        @if($viewingDepartment->subjects->count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach($viewingDepartment->subjects as $subject)
                                    <li class="py-2">
                                        <p class="font-medium">{{ $subject->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $subject->code }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No subjects assigned yet.</p>
                        @endif
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
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} Department">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-label for="name" value="Department Name" />
                <x-input id="name" wire:model="name" placeholder="Enter department name" required />
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="code" value="Department Code" />
                <x-input id="code" wire:model="code" placeholder="Enter department code" required />
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="description" value="Description" />
                <x-textarea id="description" wire:model="description" placeholder="Enter description" />
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <h1 class="text-center text-xl font-bold mb-4">Departments List</h1>
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
