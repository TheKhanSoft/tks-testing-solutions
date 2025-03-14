<?php

use App\Models\QuestionType;
use App\Services\QuestionTypeService;
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
    public bool $viewModal = false;
    public bool $addEditModal = false;
    public ?int $editingId = null;
    public ?int $viewingId = null;
    
    // QuestionType properties for create/edit
    public $name = '';
    public $description = '';
    public $instructions = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'sort_by' => ['except' => 'name'],
        'sort_dir' => ['except' => 'asc'],
    ];
    
    public function mount(QuestionTypeService $questionTypeService) 
    {
        $this->questionTypeService = $questionTypeService;
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
        $questionType = QuestionType::find($id);
        if ($questionType) {
            $this->editingId = $id;
            $this->name = $questionType->name;
            $this->description = $questionType->description;
            $this->instructions = $questionType->instructions;
            $this->addEditModal = true;
        }
    }
    
    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'instructions']);
        $this->addEditModal = true;
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:2000'
        ]);
        
        try {
            if ($this->editingId) {
                $questionType = QuestionType::findOrFail($this->editingId);
                $this->questionTypeService->updateQuestionType($questionType, $data);
                $this->success('Question type updated successfully!', position: 'toast-bottom');
            } else {
                $this->questionTypeService->createQuestionType($data);
                $this->success('Question type created successfully!', position: 'toast-bottom');
            }
            
            $this->addEditModal = false;
            $this->reset(['editingId', 'name', 'description', 'instructions']);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function delete($id): void
    {
        try {
            $questionType = QuestionType::findOrFail($id);
            $this->questionTypeService->deleteQuestionType($questionType);
            $this->success('Question type deleted successfully!', position: 'toast-bottom');
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
            ['key' => 'questions_count', 'label' => 'Questions', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
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
    
    public function getQuestionTypesWithCountProperty()
    {
        // Using withCount to avoid N+1 problem
        return QuestionType::withCount('questions')
            ->when($this->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(10);
    }
    
    public function getViewingQuestionTypeProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return QuestionType::with(['questions' => function($query) {
                $query->latest()->limit(5);
            }])
            ->withCount('questions')
            ->find($this->viewingId);
    }

    public function with(): array
    {
        return [
            'questionTypes' => $this->questionTypesWithCount,
            'viewingQuestionType' => $this->viewingQuestionType,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Question Types" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search question types..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="Create Question Type" wire:click="create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$questionTypes"  sortable wire:loading.class="opacity-50">
            
            @scope('actions', $questionType)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $questionType['id'] }})" spinner class="btn-ghost btn-sm" title="View Details" />
                    <x-button icon="o-pencil" wire:click="edit({{ $questionType['id'] }})" spinner class="btn-ghost btn-sm" title="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $questionType->id }})" wire:confirm="Are you sure you want to delete this question type?" spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                </div>
            @endscope
       
        </x-table>
        
        <div class="mt-4">
            {{ $questionTypes->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Question Types" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
            @php
                $sort_by = [
                    [ 'value' => 'name', 'name' => 'Name'],
                    [ 'value' => 'questions_count', 'name' => 'Questions Count'],
                    [ 'value' => 'created_at', 'name' => 'Created At'],
                ];
                $sort_dir = [
                    [ 'value' => 'asc', 'name' => 'Asc'],
                    [ 'value' => 'desc', 'name' => 'Desc'],
                    ]
            @endphp
                <x-icon name="o-envelope" label="Sort by" />
                <div  label="Sort By" class="flex gap-2">
                    <x-select :options="$sort_by" wire:model="sort_by"  class="w-1/3"/>
                    <x-select :options="$sort_dir" wire:model="sort_dir"  class="w-1/3"/>                    
                </div>
            </div>
        </div>
        

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Apply" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
    
    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="Question Type Details" size="lg">
        @if($viewingQuestionType)
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $viewingQuestionType->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Questions</p>
                        <p class="font-medium">{{ $viewingQuestionType->questions_count }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p>{{ $viewingQuestionType->description }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Instructions</p>
                    <p>{{ $viewingQuestionType->instructions }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Recent Questions</p>
                    @if($viewingQuestionType->questions->count() > 0)
                        <ul class="list-disc pl-5">
                            @foreach($viewingQuestionType->questions as $question)
                                <li>{{ Str::limit($question->text, 100) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No questions available.</p>
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
    <x-modal wire:model="addEditModal" title="{{ $editingId ? 'Edit' : 'Create' }} Question Type">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-label for="name" value="Name" />
                <x-input id="name" wire:model="name" placeholder="Enter question type name" required />
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="description" value="Description" />
                <x-textarea id="description" wire:model="description" placeholder="Enter description" />
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <x-label for="instructions" value="Instructions" />
                <x-textarea id="instructions" wire:model="instructions" placeholder="Enter instructions for this question type" />
                @error('instructions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <h1 class="text-center text-xl font-bold mb-4">Question Types List</h1>
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
