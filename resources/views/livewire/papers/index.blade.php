<?php

use App\Models\Paper;
use App\Models\PaperCategory;
use App\Models\Subject;
use App\Services\PaperService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public ?string $status = null;
    public ?int $paper_category_id = null;
    public ?int $subject_id = null;
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';
    public bool $drawer = false;
    public bool $exportModal = false;
    public string $exportFormat = 'pdf';
    public bool $addEditModal = false;
    public ?int $editingId = null;
    public bool $viewModal = false;
    public ?int $viewingId = null;
    
    // Paper properties for create/edit
    public $name = '';
    public $description = '';
    public $selectedSubject = null;
    public $selectedCategory = null;
    public $duration_minutes = 60;
    public $total_marks = 100;
    public $passing_percentage = 40;
    public $is_published = false;
    public $access_code = '';
    public $instructions = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'paper_category_id' => ['except' => null],
        'subject_id' => ['except' => null],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(PaperService $paperService) 
    {
        $this->paperService = $paperService;
    }
    
    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'status', 'paper_category_id', 'subject_id', 'sort_by', 'sort_dir']);
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
        $paper = Paper::find($id);
        if ($paper) {
            $this->editingId = $id;
            $this->name = $paper->name;
            $this->description = $paper->description;
            $this->selectedSubject = $paper->subject_id;
            $this->selectedCategory = $paper->paper_category_id;
            $this->duration_minutes = $paper->duration_minutes;
            $this->total_marks = $paper->total_marks;
            $this->passing_percentage = $paper->passing_percentage;
            $this->is_published = $paper->is_published;
            $this->access_code = $paper->access_code;
            $this->instructions = $paper->instructions;
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'selectedSubject', 'selectedCategory', 
                    'duration_minutes', 'total_marks', 'passing_percentage', 
                    'is_published', 'access_code', 'instructions']);
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'selectedSubject' => 'required|exists:subjects,id',
            'selectedCategory' => 'required|exists:paper_categories,id',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'total_marks' => 'nullable|integer|min:1',
            'passing_percentage' => 'nullable|integer|min:0|max:100',
            'is_published' => 'boolean',
            'access_code' => 'nullable|string|max:20',
            'instructions' => 'nullable|string|max:5000',
        ]);
        
        try {
            $paperData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'subject_id' => $data['selectedSubject'],
                'paper_category_id' => $data['selectedCategory'],
                'duration_minutes' => $data['duration_minutes'],
                'total_marks' => $data['total_marks'],
                'passing_percentage' => $data['passing_percentage'],
                'is_published' => $data['is_published'],
                'access_code' => $data['access_code'],
                'instructions' => $data['instructions'],
            ];
            
            if ($this->editingId) {
                $paper = Paper::findOrFail($this->editingId);
                $this->paperService->updatePaper($paper, $paperData);
                $this->success('Paper updated successfully!', position: 'toast-bottom');
            } else {
                $this->paperService->createPaper($paperData);
                $this->success('Paper created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'description', 'selectedSubject', 'selectedCategory', 
                        'duration_minutes', 'total_marks', 'passing_percentage', 
                        'is_published', 'access_code', 'instructions']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $paper = Paper::findOrFail($id);
            $this->paperService->deletePaper($paper);
            $this->success('Paper deleted successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function publish($id): void
    {
        try {
            $paper = Paper::findOrFail($id);
            if ($paper->questions()->count() === 0) {
                $this->error('Cannot publish a paper without questions!', position: 'toast-bottom');
                return;
            }
            
            $this->paperService->publishPaper($paper);
            $this->success('Paper published successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function archive($id): void
    {
        try {
            $paper = Paper::findOrFail($id);
            $this->paperService->archivePaper($paper);
            $this->success('Paper archived successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function export(): void
    {
        try {
            $filters = [
                'status' => $this->status,
                'paper_category_id' => $this->paper_category_id,
                'subject_id' => $this->subject_id,
                'search' => $this->search,
                'sort_by' => $this->sort_by,
                'sort_dir' => $this->sort_dir,
            ];
            
            $path = $this->paperService->exportPapers($this->exportFormat, $filters);
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
            ['key' => 'subject', 'label' => 'Subject', 'sortable' => false],
            ['key' => 'category', 'label' => 'Category', 'sortable' => false],
            ['key' => 'total_marks', 'label' => 'Marks', 'sortable' => true],
            ['key' => 'duration_minutes', 'label' => 'Duration (min)', 'sortable' => true],
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
    
    public function getPaperCategoriesProperty()
    {
        return PaperCategory::orderBy('name')->get(['id', 'name']);
    }
    
    public function getSubjectsProperty()
    {
        return Subject::orderBy('name')->get(['id', 'name']);
    }
    
    public function getPapersProperty()
    {
        $filters = [
            'status' => $this->status,
            'paper_category_id' => $this->paper_category_id,
            'subject_id' => $this->subject_id,
            'search' => $this->search,
            'sort_by' => $this->sort_by,
            'sort_dir' => $this->sort_dir,
        ];
        
        // Pass the perPage value as first argument, then filters as second argument
        return $this->paperService->getPaginatedPapers(15, $filters);
    }
    
    public function getViewingPaperProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return Paper::with([
                'subject',
                'paperCategory',
                'questions' => function($query) {
                    $query->orderBy('order_index')->limit(10);
                },
                'userCategories'
            ])
            ->withCount('questions', 'testAttempts')
            ->find($this->viewingId);
    }

    public function with(): array
    {
        return [
            'papers' => $this->papers,
            'paperCategories' => $this->paperCategories,
            'subjects' => $this->subjects,
            'viewingPaper' => $this->viewingPaper,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Papers Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search papers..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Export" @click="$wire.exportModal = true" responsive icon="o-arrow-down-tray" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Create Paper" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$papers" sortable wire:loading.class="opacity-50">
            @scope('cell_status', $paper)
                <x-badge :value="$paper->status" 
                         :color="$paper->status === 'published' ? 'success' : ($paper->status === 'draft' ? 'warning' : 'neutral')" />
            @endscope

            @scope('cell_total_marks', $paper)
                {{ $paper->total_marks }}
                <p class="text-xs text-gray-500">{{ $paper->duration_minutes }} min</p>
            @endscope

            @scope('cell_subject', $paper)
                {{ $paper->subject->name ?? 'N/A' }}
            @endscope

            @scope('actions', $paper)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $paper->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                    <x-button icon="o-pencil" wire:click="edit({{ $paper->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                    
                    @if($paper->status === 'draft')
                        <x-button icon="o-check-circle" wire:click="publish({{ $paper->id }})" 
                            wire:confirm="Are you sure you want to publish this paper?" 
                            spinner class="btn-ghost btn-sm text-green-500" title="Publish" />
                    @elseif($paper->status === 'published')
                        <x-button icon="o-archive-box" wire:click="archive({{ $paper->id }})" 
                            wire:confirm="Are you sure you want to archive this paper?" 
                            spinner class="btn-ghost btn-sm text-yellow-500" title="Archive" />
                    @endif
                    
                    <x-button icon="o-trash" wire:click="delete({{ $paper->id }})" 
                        wire:confirm="Are you sure you want to delete this paper?" 
                        spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $papers->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Papers" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="status" value="Status" />
                <x-select wire:model.live="status" placeholder="Select Status" clearable>
                    <x-select.option value="draft" label="Draft" />
                    <x-select.option value="published" label="Published" />
                    <x-select.option value="archived" label="Archived" />
                </x-select>
            </div>
            
            <div>
                <x-label for="paper_category_id" value="Paper Category" />
                <x-select wire:model.live="paper_category_id" placeholder="Select Category" clearable>
                    @foreach($paperCategories as $category)
                        <x-select.option value="{{ $category->id }}" label="{{ $category->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="subject_id" value="Subject" />
                <x-select wire:model.live="subject_id" placeholder="Select Subject" clearable>
                    @foreach($subjects as $subject)
                        <x-select.option value="{{ $subject->id }}" label="{{ $subject->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="name" label="Name" />
                        <x-select.option value="total_marks" label="Total Marks" />
                        <x-select.option value="duration_minutes" label="Duration" />
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
    <x-modal wire:model="exportModal" title="Export Papers">
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
    <x-modal wire:model="viewModal" title="Paper Details" size="lg">
        @if($viewingPaper)
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $viewingPaper->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Subject</p>
                        <p class="font-medium">{{ $viewingPaper->subject->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Category</p>
                        <p class="font-medium">{{ $viewingPaper->paperCategory->name }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Marks</p>
                        <p class="font-medium">{{ $viewingPaper->total_marks }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Duration</p>
                        <p class="font-medium">{{ $viewingPaper->duration_minutes }} minutes</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Passing %</p>
                        <p class="font-medium">{{ $viewingPaper->passing_percentage }}%</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst($viewingPaper->status) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Questions</p>
                        <p class="font-medium">{{ $viewingPaper->questions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Test Attempts</p>
                        <p class="font-medium">{{ $viewingPaper->test_attempts_count }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p>{{ $viewingPaper->description }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Instructions</p>
                    <p>{{ $viewingPaper->instructions }}</p>
                </div>
                
                @if($viewingPaper->access_code)
                <div>
                    <p class="text-sm text-gray-500">Access Code</p>
                    <p class="font-medium">{{ $viewingPaper->access_code }}</p>
                </div>
                @endif
                
                <div>
                    <p class="text-sm text-gray-500">User Categories</p>
                    @if($viewingPaper->userCategories->count() > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($viewingPaper->userCategories as $userCategory)
                                <x-badge value="{{ $userCategory->name }}" />
                            @endforeach
                        </div>
                    @else
                        <p>Not assigned to any user category</p>
                    @endif
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Questions (showing first 10)</p>
                    @if($viewingPaper->questions->count() > 0)
                        <ul class="list-decimal pl-5 mt-2">
                            @foreach($viewingPaper->questions as $question)
                                <li class="mb-1">{{ Str::limit($question->text, 100) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No questions available</p>
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
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} Paper" size="lg">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="name" value="Paper Name" />
                    <x-input id="name" wire:model="name" placeholder="Enter paper name" required />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="selectedSubject" value="Subject" />
                    <x-select id="selectedSubject" wire:model="selectedSubject" placeholder="Select Subject" required>
                        @foreach($subjects as $subject)
                            <x-select.option value="{{ $subject->id }}" label="{{ $subject->name }}" />
                        @endforeach
                    </x-select>
                    @error('selectedSubject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="selectedCategory" value="Paper Category" />
                    <x-select id="selectedCategory" wire:model="selectedCategory" placeholder="Select Category" required>
                        @foreach($paperCategories as $category)
                            <x-select.option value="{{ $category->id }}" label="{{ $category->name }}" />
                        @endforeach
                    </x-select>
                    @error('selectedCategory') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="access_code" value="Access Code (Optional)" />
                    <x-input id="access_code" wire:model="access_code" placeholder="Enter access code if needed" />
                    @error('access_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-label for="duration_minutes" value="Duration (minutes)" />
                    <x-input id="duration_minutes" type="number" wire:model="duration_minutes" min="1" max="600" />
                    @error('duration_minutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="total_marks" value="Total Marks" />
                    <x-input id="total_marks" type="number" wire:model="total_marks" min="1" />
                    @error('total_marks') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <x-label for="passing_percentage" value="Passing Percentage" />
                    <x-input id="passing_percentage" type="number" wire:model="passing_percentage" min="0" max="100" />
                    @error('passing_percentage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div>
                <x-label for="description" value="Description" />
                <x-textarea id="description" wire:model="description" placeholder="Enter description" />
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="instructions" value="Instructions" />
                <x-textarea id="instructions" wire:model="instructions" placeholder="Enter instructions for test takers" />
                @error('instructions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-checkbox id="is_published" label="Publish immediately" wire:model="is_published" />
                @error('is_published') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <h1 class="text-center text-xl font-bold mb-4">Papers List</h1>
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
