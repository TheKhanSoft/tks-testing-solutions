<?php

use App\Models\Subject;
use App\Models\Department;
use App\Services\SubjectService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public ?string $status = null;
    public ?int $department_id = null;
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';
    public bool $drawer = false;
    public bool $exportModal = false;
    public string $exportFormat = 'pdf';
    public bool $addEditModal = false;
    public ?int $editingId = null;
    
    // Subject properties for create/edit
    public $name = '';
    public $code = '';
    public $description = '';
    public $subject_status = 'active';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'department_id' => ['except' => null],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(SubjectService $subjectService) 
    {
        $this->subjectService = $subjectService;
    }
    
    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'status', 'department_id', 'sort_by', 'sort_dir']);
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    public function edit($id): void
    {
        $subject = Subject::find($id);
        if ($subject) {
            $this->editingId = $id;
            $this->name = $subject->name;
            $this->code = $subject->code;
            $this->description = $subject->description;
            $this->subject_status = $subject->status;
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'code', 'description', 'subject_status']);
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:2000',
            'subject_status' => 'required|in:active,inactive'
        ]);
        
        try {
            if ($this->editingId) {
                $subject = Subject::findOrFail($this->editingId);
                $this->subjectService->updateSubject($subject, [
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'status' => $data['subject_status'],
                ]);
                $this->success('Subject updated successfully!', position: 'toast-bottom');
            } else {
                $this->subjectService->createSubject([
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'status' => $data['subject_status'],
                ]);
                $this->success('Subject created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'code', 'description', 'subject_status']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $subject = Subject::findOrFail($id);
            $this->subjectService->deleteSubject($subject);
            $this->success('Subject deleted successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function export(): void
    {
        try {
            $filters = [
                'department_id' => $this->department_id,
                'status' => $this->status,
                'search' => $this->search,
                'sort_by' => $this->sort_by,
                'sort_dir' => $this->sort_dir,
            ];
            
            $path = $this->subjectService->exportSubjects($this->exportFormat, $filters);
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
            ['key' => 'code', 'label' => 'Code', 'sortable' => true],
            ['key' => 'description', 'label' => 'Description', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
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
    
    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }
    
    public function getSubjectsProperty()
    {
        $filters = [
            'department_id' => $this->department_id,
            'status' => $this->status,
            'search' => $this->search,
            'sort_by' => $this->sort_by,
            'sort_dir' => $this->sort_dir,
        ];
        
        // Use the service with eager loading to avoid N+1 problems
        return $this->subjectService->getPaginatedSubjects(
            $filters, 
            15, 
            ['*'], 
            ['department'] // Eager load departments to avoid N+1 query issues
        );
    }

    public function with(): array
    {
        return [
            'subjects' => $this->subjects,
            'department' => $this->department,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Subjects Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search subjects..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Export" @click="$wire.exportModal = true" responsive icon="o-arrow-down-tray" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Create Subject" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$subjects" sortable wire:loading.class="opacity-50">
            @scope('cell_status', $subject)
                <x-badge :value="$subject->status" 
                         :color="$subject->status === 'active' ? 'success' : 'warning'" />
            @endscope

            @scope('cell_department', $subject)
                {{ $subject->department->name ?? 'N/A' }}
            @endscope

            @scope('cell_description', $subject)
                {{ Str::limit($subject->description, 50) }}
            @endscope

            @scope('actions', $subject)
                <div class="flex gap-1">
                    <x-button icon="o-pencil" wire:click="edit({{ $subject->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                    <x-dropdown>
                        <x-dropdown.item label="More Actions" disabled />
                        @if($subject->status === 'active')
                            <x-dropdown.item icon="o-lock-closed" label="Deactivate" wire:click="deactivateSubject({{ $subject->id }})" 
                                wire:confirm="Are you sure you want to deactivate this subject?" />
                        @else
                            <x-dropdown.item icon="o-lock-open" label="Activate" wire:click="activateSubject({{ $subject->id }})" />
                        @endif
                        <x-dropdown.item icon="o-trash" label="Delete" class="text-red-500" wire:click="delete({{ $subject->id }})"
                            wire:confirm="Are you sure you want to delete this subject? This action cannot be undone." />
                    </x-dropdown>
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $subjects->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Subjects" right separator with-close-button class="lg:w-1/3">
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
                <x-label for="department_id" />
                <x-select wire:model.live="department_id" label="Department" placeholder="Select Department" clearable>
                    
                </x-select>
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="name" label="Name" />
                        <x-select.option value="code" label="Code" />
                        <x-select.option value="status" label="Status" />
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
    <x-modal wire:model="exportModal" title="Export Subjects">
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
    
    <!-- ADD/EDIT MODAL -->
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} Subject">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-label for="name" value="Subject Name" />
                <x-input id="name" wire:model="name" placeholder="Enter subject name" required />
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="code" value="Subject Code" />
                <x-input id="code" wire:model="code" placeholder="Enter subject code" required />
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="description" value="Description" />
                <x-textarea id="description" wire:model="description" placeholder="Enter description" />
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="subject_status" value="Status" />
                <x-select id="subject_status" wire:model="subject_status">
                    <x-select.option value="active" label="Active" />
                    <x-select.option value="inactive" label="Inactive" />
                </x-select>
                @error('subject_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <h1 class="text-center text-xl font-bold mb-4">Subjects List</h1>
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
