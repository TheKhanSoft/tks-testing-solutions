<?php

use App\Models\FacultyMember;
use App\Models\Department;
use App\Services\FacultyMemberService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public ?string $status = null;
    public ?int $department_id = null;
    public ?string $role = null;
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';
    public bool $drawer = false;
    public bool $exportModal = false;
    public string $exportFormat = 'pdf';
    public bool $addEditModal = false;
    public ?int $editingId = null;
    public bool $viewModal = false;
    public ?int $viewingId = null;
    
    // Faculty member properties for create/edit
    public $name = '';
    public $email = '';
    public $phone = '';
    public $selectedDepartment = null;
    public $faculty_status = 'active';
    public $password = '';
    public $selectedRoles = [];
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'department_id' => ['except' => null],
        'role' => ['except' => null],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(FacultyMemberService $facultyMemberService) 
    {
        $this->facultyMemberService = $facultyMemberService;
    }
    
    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'status', 'department_id', 'role', 'sort_by', 'sort_dir']);
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
        $faculty = FacultyMember::with('roles')->find($id);
        if ($faculty) {
            $this->editingId = $id;
            $this->name = $faculty->name;
            $this->email = $faculty->email;
            $this->phone = $faculty->phone;
            $this->selectedDepartment = $faculty->department_id;
            $this->faculty_status = $faculty->status;
            $this->password = '';
            $this->selectedRoles = $faculty->roles->pluck('id')->toArray();
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'selectedDepartment', 
                     'faculty_status', 'password', 'selectedRoles']);
        $this->faculty_status = 'active';
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'selectedDepartment' => 'required|exists:departments,id',
            'faculty_status' => 'required|in:active,inactive',
            'selectedRoles' => 'array'
        ];
        
        if ($this->editingId) {
            $rules['email'] .= '|unique:faculty_members,email,' . $this->editingId;
            $rules['password'] = 'nullable|min:8';
        } else {
            $rules['email'] .= '|unique:faculty_members,email';
            $rules['password'] = 'required|min:8';
        }
        
        $data = $this->validate($rules);
        
        try {
            $facultyData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'department_id' => $data['selectedDepartment'],
                'status' => $data['faculty_status']
            ];
            
            if ($this->password) {
                $facultyData['password'] = $this->password;
            }
            
            if ($this->editingId) {
                $faculty = FacultyMember::findOrFail($this->editingId);
                $this->facultyMemberService->updateFacultyMember($faculty, $facultyData);
                
                // Update roles
                if (count($this->selectedRoles) > 0) {
                    $faculty->syncRoles($this->selectedRoles);
                }
                
                $this->success('Faculty member updated successfully!', position: 'toast-bottom');
            } else {
                $faculty = $this->facultyMemberService->createFacultyMember($facultyData);
                
                // Assign roles
                if (count($this->selectedRoles) > 0) {
                    $faculty->syncRoles($this->selectedRoles);
                }
                
                $this->success('Faculty member created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'email', 'phone', 'selectedDepartment', 
                         'faculty_status', 'password', 'selectedRoles']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $faculty = FacultyMember::findOrFail($id);
            
            // Check if faculty has subjects or other dependencies
            if ($faculty->subjects()->exists()) {
                throw new \Exception('Cannot delete faculty member with assigned subjects. Please reassign subjects first.');
            }
            
            $this->facultyMemberService->deleteFacultyMember($faculty);
            $this->success('Faculty member deleted successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function export(): void
    {
        try {
            $filters = [
                'status' => $this->status,
                'department_id' => $this->department_id,
                'role' => $this->role,
                'search' => $this->search,
                'sort_by' => $this->sort_by,
                'sort_dir' => $this->sort_dir,
            ];
            
            $path = $this->facultyMemberService->exportFacultyMembers($this->exportFormat, $filters);
            $this->exportModal = false;
            $this->success('Export successful! Downloading...', position: 'toast-bottom');
            
            // Trigger download via JavaScript
            $this->dispatch('triggerDownload', ['url' => $path]);
        } catch (\Exception $e) {
            $this->error('Export failed: ' . $e->getMessage(), position: 'toast-bottom');
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
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'phone', 'label' => 'Phone', 'sortable' => false],
            ['key' => 'department', 'label' => 'Department', 'sortable' => false],
            ['key' => 'subjects_count', 'label' => 'Subjects', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
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
    
    public function getDepartmentsProperty()
    {
        // Cache departments to improve performance
        return cache()->remember('departments_list', now()->addHours(1), function() {
            return Department::select(['id', 'name'])->orderBy('name')->get();
        });
    }
    
    public function getRolesProperty()
    {
        // Cache roles to improve performance
        return cache()->remember('roles_list', now()->addHours(24), function() {
            return \Spatie\Permission\Models\Role::select(['id', 'name'])->orderBy('name')->get();
        });
    }
    
    public function getFacultyMembersProperty()
    {
        // Optimize query with eager loading to prevent N+1 issues
        return FacultyMember::with(['department:id,name', 'roles:id,name'])
            ->withCount('subjects')
            ->when($this->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $search = '%' . $search . '%';
                    $q->where('name', 'like', $search)
                      ->orWhere('email', 'like', $search)
                      ->orWhere('phone', 'like', $search);
                });
            })
            ->when($this->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($this->department_id, function($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($this->role, function($query, $role) {
                return $query->role($role);
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(10);
    }
    
    public function getViewingFacultyMemberProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return FacultyMember::with([
                'department:id,name',
                'roles:id,name',
                'subjects' => function($query) {
                    $query->select(['subjects.id', 'subjects.name', 'subjects.code'])->take(5);
                }
            ])
            ->withCount('subjects')
            ->find($this->viewingId);
    }

    public function with(): array
    {
        return [
            'facultyMembers' => $this->facultyMembers,
            'departments' => $this->departments,
            'roles' => $this->roles,
            'viewingFacultyMember' => $this->viewingFacultyMember,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Faculty Members" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search faculty members..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Export" @click="$wire.exportModal = true" responsive icon="o-arrow-down-tray" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Add Faculty" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" sortable wire:loading.class="opacity-50">
            @foreach($facultyMembers as $faculty)
                <tr>
                    <td>{{ $faculty->id }}</td>
                    <td>{{ $faculty->name }}</td>
                    <td>{{ $faculty->email }}</td>
                    <td>{{ $faculty->phone ?? 'N/A' }}</td>
                    <td>{{ $faculty->department->name ?? 'N/A' }}</td>
                    <td>{{ $faculty->subjects_count }}</td>
                    <td>
                        <x-badge :value="$faculty->status" 
                                 :color="$faculty->status === 'active' ? 'success' : 'warning'" />
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <x-button icon="o-eye" wire:click="view({{ $faculty->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                            <x-button icon="o-pencil" wire:click="edit({{ $faculty->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                            <x-button icon="o-trash" wire:click="delete({{ $faculty->id }})" wire:confirm="Are you sure you want to delete this faculty member?" spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        
        <div class="mt-4">
            {{ $facultyMembers->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Faculty" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="status" value="Status" />
                <x-select wire:model.live="status" placeholder="Select Status" clearable>
                    <x-select.option value="active" label="Active" />
                    <x-select.option value="inactive" label="Inactive" />
                </x-select>
            </div>
            
            <div>
                <x-label for="department_id" value="Department" />
                <x-select wire:model.live="department_id" placeholder="Select Department" clearable>
                    @foreach($departments as $department)
                        <x-select.option value="{{ $department->id }}" label="{{ $department->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="role" value="Role" />
                <x-select wire:model.live="role" placeholder="Select Role" clearable>
                    @foreach($roles as $role)
                        <x-select.option value="{{ $role->name }}" label="{{ $role->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="name" label="Name" />
                        <x-select.option value="email" label="Email" />
                        <x-select.option value="status" label="Status" />
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

    <!-- EXPORT MODAL -->
    <x-modal wire:model="exportModal" title="Export Faculty Members">
        <div class="mb-4">
            <x-label for="exportFormat" value="Select Format" />
            <x-select wire:model="exportFormat">
                <x-select.option value="pdf" label="PDF" />
                <x-select.option value="xlsx" label="Excel (XLSX)" />
                <x-select.option value="csv" label="CSV" />
            </x-select>
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.exportModal = false" />
            <x-button label="Export" wire:click="export" spinner class="btn-primary" />
        </x-slot:actions>
    </x-modal>

    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="Faculty Member Details" size="lg">
        @if($viewingFacultyMember)
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $viewingFacultyMember->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $viewingFacultyMember->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $viewingFacultyMember->phone ?? 'Not provided' }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Department</p>
                        <p class="font-medium">{{ $viewingFacultyMember->department->name ?? 'None' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <x-badge :value="$viewingFacultyMember->status" 
                                 :color="$viewingFacultyMember->status === 'active' ? 'success' : 'warning'" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Joined</p>
                        <p class="font-medium">{{ $viewingFacultyMember->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Roles</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($viewingFacultyMember->roles as $role)
                            <x-badge value="{{ $role->name }}" />
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-sm text-gray-500">Subjects ({{ $viewingFacultyMember->subjects_count }})</p>
                        <a href="{{ route('faculty.subjects', $viewingFacultyMember->id) }}" class="text-sm text-blue-500">View All</a>
                    </div>
                    @if($viewingFacultyMember->subjects->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($viewingFacultyMember->subjects as $subject)
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
        @endif
        
        <x-slot:actions>
            <x-button label="Close" @click="$wire.viewModal = false" />
            <x-button label="Edit" wire:click="edit({{ $viewingId }})" @click="$wire.viewModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
    
    <!-- ADD/EDIT MODAL -->
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} Faculty Member" size="lg">
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="name" value="Name" />
                    <x-input id="name" wire:model="name" placeholder="Enter full name" required />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="email" value="Email" />
                    <x-input id="email" type="email" wire:model="email" placeholder="Enter email address" required />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="phone" value="Phone (Optional)" />
                    <x-input id="phone" wire:model="phone" placeholder="Enter phone number" />
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="selectedDepartment" value="Department" />
                    <x-select id="selectedDepartment" wire:model="selectedDepartment" placeholder="Select Department" required>
                        @foreach($departments as $department)
                            <x-select.option value="{{ $department->id }}" label="{{ $department->name }}" />
                        @endforeach
                    </x-select>
                    @error('selectedDepartment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="password" value="{{ $editingId ? 'Password (Leave blank to keep current)' : 'Password' }}" />
                    <x-input id="password" type="password" wire:model="password" placeholder="{{ $editingId ? 'Enter new password' : 'Enter password' }}" {{ $editingId ? '' : 'required' }} />
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="faculty_status" value="Status" />
                    <x-select id="faculty_status" wire:model="faculty_status">
                        <x-select.option value="active" label="Active" />
                        <x-select.option value="inactive" label="Inactive" />
                    </x-select>
                    @error('faculty_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div>
                <x-label for="selectedRoles" value="Roles" />
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mt-2">
                    @foreach($roles as $role)
                        <label class="cursor-pointer flex items-start gap-2">
                            <input type="checkbox" class="checkbox" 
                                   value="{{ $role->id }}" 
                                   wire:model="selectedRoles" />
                            <span>{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </form>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.addEditModal = false" />
            <x-button label="{{ $editingId ? 'Update' : 'Create' }}" wire:click="save" spinner class="btn-primary" />
        </x-slot:actions>
    </x-modal>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('triggerDownload', (data) => {
                window.open(data.url, '_blank');
            });
            
            @this.on('printTable', () => {
                const printContents = document.getElementById('printable-table').innerHTML;
                const originalContents = document.body.innerHTML;
                
                document.body.innerHTML = `
                    <div class="print-container">
                        <h1 class="text-center text-xl font-bold mb-4">Faculty Members List</h1>
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
