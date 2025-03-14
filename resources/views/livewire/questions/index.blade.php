<?php

use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use App\Services\QuestionService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination, WithFileUploads;

    public string $search = '';
    public ?string $difficulty = null;
    public ?int $subject_id = null;
    public ?int $question_type_id = null;
    public string $sort_by = 'created_at';
    public string $sort_dir = 'desc';
    public bool $drawer = false;
    public bool $exportModal = false;
    public string $exportFormat = 'pdf';
    public bool $importModal = false;
    public $importFile = null;
    public bool $viewModal = false;
    public ?int $viewingId = null;
    public bool $bulkEditMode = false;
    public array $selectedQuestions = [];
    public $bulkActions = [
        'mark_as_active' => 'Mark as Active',
        'mark_as_inactive' => 'Mark as Inactive',
        'change_difficulty' => 'Change Difficulty',
        'change_subject' => 'Change Subject',
        'change_type' => 'Change Type',
        'delete' => 'Delete'
    ];
    public $selectedBulkAction = null;
    public $bulkSubject = null;
    public $bulkQuestionType = null;
    public $bulkDifficulty = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'difficulty' => ['except' => null],
        'subject_id' => ['except' => null],
        'question_type_id' => ['except' => null],
        'sort_by' => ['except' => 'created_at'],
        'sort_dir' => ['except' => 'desc'],
    ];
    
    public function mount(QuestionService $questionService) 
    {
        $this->questionService = $questionService;
    }
    
    public function clear(): void
    {
        $this->reset(['search', 'difficulty', 'subject_id', 'question_type_id', 'sort_by', 'sort_dir']);
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    public function view($id): void
    {
        $this->viewingId = $id;
        $this->viewModal = true;
    }
    
    public function toggleBulkEditMode(): void
    {
        $this->bulkEditMode = !$this->bulkEditMode;
        if (!$this->bulkEditMode) {
            $this->selectedQuestions = [];
        }
    }
    
    public function applyBulkAction(): void
    {
        if (empty($this->selectedQuestions)) {
            $this->error('No questions selected.', position: 'toast-bottom');
            return;
        }
        
        if (empty($this->selectedBulkAction)) {
            $this->error('No action selected.', position: 'toast-bottom');
            return;
        }
        
        try {
            $count = count($this->selectedQuestions);
            
            switch ($this->selectedBulkAction) {
                case 'mark_as_active':
                    $this->questionService->bulkUpdateStatus($this->selectedQuestions, 'active');
                    $this->success("Updated $count questions to active status.", position: 'toast-bottom');
                    break;
                    
                case 'mark_as_inactive':
                    $this->questionService->bulkUpdateStatus($this->selectedQuestions, 'inactive');
                    $this->success("Updated $count questions to inactive status.", position: 'toast-bottom');
                    break;
                    
                case 'change_difficulty':
                    if (empty($this->bulkDifficulty)) {
                        $this->error('Please select a difficulty level.', position: 'toast-bottom');
                        return;
                    }
                    $this->questionService->bulkUpdateDifficulty($this->selectedQuestions, $this->bulkDifficulty);
                    $this->success("Updated difficulty for $count questions.", position: 'toast-bottom');
                    break;
                    
                case 'change_subject':
                    if (empty($this->bulkSubject)) {
                        $this->error('Please select a subject.', position: 'toast-bottom');
                        return;
                    }
                    $this->questionService->bulkUpdateSubject($this->selectedQuestions, $this->bulkSubject);
                    $this->success("Updated subject for $count questions.", position: 'toast-bottom');
                    break;
                    
                case 'change_type':
                    if (empty($this->bulkQuestionType)) {
                        $this->error('Please select a question type.', position: 'toast-bottom');
                        return;
                    }
                    $this->questionService->bulkUpdateQuestionType($this->selectedQuestions, $this->bulkQuestionType);
                    $this->success("Updated question type for $count questions.", position: 'toast-bottom');
                    break;
                    
                case 'delete':
                    $this->questionService->bulkDelete($this->selectedQuestions);
                    $this->success("Deleted $count questions.", position: 'toast-bottom');
                    break;
                    
                default:
                    $this->error('Invalid action selected.', position: 'toast-bottom');
                    return;
            }
            
            $this->bulkEditMode = false;
            $this->selectedQuestions = [];
            $this->selectedBulkAction = null;
            $this->bulkSubject = null;
            $this->bulkQuestionType = null;
            $this->bulkDifficulty = null;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function export(): void
    {
        try {
            $filters = [
                'difficulty' => $this->difficulty,
                'subject_id' => $this->subject_id,
                'question_type_id' => $this->question_type_id,
                'search' => $this->search,
                'sort_by' => $this->sort_by,
                'sort_dir' => $this->sort_dir,
            ];
            
            $path = $this->questionService->exportQuestions($this->exportFormat, $filters);
            $this->exportModal = false;
            $this->success('Export successful! Downloading...', position: 'toast-bottom');
            
            $this->dispatch('triggerDownload', ['url' => $path]);
        } catch (\Exception $e) {
            $this->error('Export failed: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function import(): void
    {
        try {
            $this->validate([
                'importFile' => 'required|file|mimes:csv,xlsx|max:10240', // max 10MB
            ]);
            
            $result = $this->questionService->importQuestions($this->importFile->getRealPath());
            $this->importModal = false;
            $this->reset('importFile');
            
            if ($result['success']) {
                $this->success("Successfully imported {$result['count']} questions.", position: 'toast-bottom');
            } else {
                $this->error("Import completed with errors. {$result['success_count']} imported, {$result['error_count']} failed.", position: 'toast-bottom');
                
                // Store error log for download
                if (!empty($result['error_log'])) {
                    session(['import_error_log' => $result['error_log']]);
                    $this->dispatch('showErrorLogDownload');
                }
            }
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function print()
    {
        $this->dispatch('printTable');
    }
    
    public function headers(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#', 'sortable' => true],
            ['key' => 'text', 'label' => 'Question', 'sortable' => true],
            ['key' => 'type', 'label' => 'Type', 'sortable' => false],
            ['key' => 'subject', 'label' => 'Subject', 'sortable' => false],
            ['key' => 'difficulty', 'label' => 'Difficulty', 'sortable' => true],
            ['key' => 'marks', 'label' => 'Marks', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true]
        ];
        
        if ($this->bulkEditMode) {
            array_unshift($headers, ['key' => 'select', 'label' => 'Select', 'sortable' => false]);
        }
        
        return $headers;
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
    
    public function selectAllQuestions(): void
    {
        if (count($this->selectedQuestions) === $this->questions->count()) {
            $this->selectedQuestions = [];
        } else {
            $this->selectedQuestions = $this->questions->pluck('id')->toArray();
        }
    }
    
    public function getSubjectsProperty()
    {
        return cache()->remember('subjects_list_for_questions', now()->addHour(), function() {
            return Subject::select(['id', 'name'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        });
    }
    
    public function getQuestionTypesProperty()
    {
        return cache()->remember('question_types_list', now()->addHour(), function() {
            return QuestionType::select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });
    }
    
    public function getQuestionsProperty()
    {
        return Question::with(['subject:id,name', 'questionType:id,name'])
            ->when($this->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('text', 'like', "%{$search}%");
                });
            })
            ->when($this->difficulty, function($query, $difficulty) {
                return $query->where('difficulty', $difficulty);
            })
            ->when($this->subject_id, function($query, $subject_id) {
                return $query->where('subject_id', $subject_id);
            })
            ->when($this->question_type_id, function($query, $question_type_id) {
                return $query->where('question_type_id', $question_type_id);
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(15);
    }
    
    public function getViewingQuestionProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        return Question::with([
                'subject:id,name', 
                'questionType:id,name',
                'options',
                'papers:id,name'
            ])
            ->withCount('papers')
            ->find($this->viewingId);
    }
    
    public function getDifficultyLevelsProperty()
    {
        return [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];
    }

    public function with(): array
    {
        return [
            'questions' => $this->questions,
            'subjects' => $this->subjects,
            'questionTypes' => $this->questionTypes,
            'viewingQuestion' => $this->viewingQuestion,
            'difficultyLevels' => $this->difficultyLevels,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Questions Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search questions..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Export" @click="$wire.exportModal = true" responsive icon="o-arrow-down-tray" />
            <x-button label="Import" @click="$wire.importModal = true" responsive icon="o-arrow-up-tray" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
            <x-button label="{{ $bulkEditMode ? 'Exit Bulk Edit' : 'Bulk Edit' }}" wire:click="toggleBulkEditMode" 
                    responsive icon="{{ $bulkEditMode ? 'o-x-mark' : 'o-check-circle' }}" 
                    class="{{ $bulkEditMode ? 'btn-error' : 'btn-info' }}" />
            <x-button label="Create Question" href="{{ route('questions.create') }}" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- BULK ACTIONS BAR (only visible when bulk edit is active) -->
    @if($bulkEditMode)
        <div class="bg-base-200 p-4 rounded-lg mb-4 flex flex-wrap items-center gap-3">
            <div class="font-medium">Bulk Actions:</div>
            <div class="flex-1 flex flex-wrap gap-2 items-center">
                <x-select wire:model="selectedBulkAction" placeholder="Select Action" class="max-w-xs">
                    @foreach($bulkActions as $value => $label)
                        <x-select.option :value="$value" :label="$label" />
                    @endforeach
                </x-select>
                
                @if($selectedBulkAction === 'change_difficulty')
                    <x-select wire:model="bulkDifficulty" placeholder="Select Difficulty" class="max-w-xs">
                        @foreach($difficultyLevels as $value => $label)
                            <x-select.option :value="$value" :label="$label" />
                        @endforeach
                    </x-select>
                @endif
                
                @if($selectedBulkAction === 'change_subject')
                    <x-select wire:model="bulkSubject" placeholder="Select Subject" class="max-w-xs">
                        @foreach($subjects as $subject)
                            <x-select.option :value="$subject->id" :label="$subject->name" />
                        @endforeach
                    </x-select>
                @endif
                
                @if($selectedBulkAction === 'change_type')
                    <x-select wire:model="bulkQuestionType" placeholder="Select Question Type" class="max-w-xs">
                        @foreach($questionTypes as $type)
                            <x-select.option :value="$type->id" :label="$type->name" />
                        @endforeach
                    </x-select>
                @endif
                
                <x-button label="Apply" wire:click="applyBulkAction" 
                         wire:confirm="Are you sure you want to apply this action to all selected questions?"
                         class="btn-primary" 
                         :disabled="empty($selectedQuestions) || empty($selectedBulkAction)" />
                         
                <div class="text-sm">
                    {{ count($selectedQuestions) }} questions selected
                </div>
                
                <div class="ml-auto">
                    <x-button icon="o-check" 
                             wire:click="selectAllQuestions" 
                             class="btn-sm btn-outline"
                             label="{{ count($selectedQuestions) === $questions->count() ? 'Unselect All' : 'Select All' }}" />
                </div>
            </div>
        </div>
    @endif

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" sortable wire:loading.class="opacity-50">
            @foreach($questions as $question)
                <tr>
                    @if($bulkEditMode)
                        <td>
                            <x-checkbox wire:model.live="selectedQuestions" value="{{ $question->id }}" />
                        </td>
                    @endif
                    <td>{{ $question->id }}</td>
                    <td>
                        <div class="max-w-sm">
                            {{ Str::limit(strip_tags($question->text), 80) }}
                        </div>
                    </td>
                    <td>{{ $question->questionType->name ?? 'N/A' }}</td>
                    <td>{{ $question->subject->name ?? 'N/A' }}</td>
                    <td>
                        <x-badge :value="ucfirst($question->difficulty)" 
                                :color="$question->difficulty === 'easy' ? 'success' : ($question->difficulty === 'medium' ? 'warning' : 'error')" />
                    </td>
                    <td>{{ $question->marks }}</td>
                    <td>
                        <x-badge :value="$question->status" 
                                :color="$question->status === 'active' ? 'success' : 'warning'" />
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <x-button icon="o-eye" wire:click="view({{ $question->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                            <x-button icon="o-pencil" href="{{ route('questions.edit', $question->id) }}" class="btn-ghost btn-sm" title="Edit" />
                            <x-button icon="o-trash" wire:click="$dispatch('openModal', { component: 'modals.delete-question', arguments: { questionId: {{ $question->id }} }})" spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        
        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Questions" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="difficulty" value="Difficulty" />
                <x-select wire:model.live="difficulty" placeholder="Select Difficulty" clearable>
                    @foreach($difficultyLevels as $value => $label)
                        <x-select.option :value="$value" :label="$label" />
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
                <x-label for="question_type_id" value="Question Type" />
                <x-select wire:model.live="question_type_id" placeholder="Select Question Type" clearable>
                    @foreach($questionTypes as $type)
                        <x-select.option value="{{ $type->id }}" label="{{ $type->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="created_at" label="Date Created" />
                        <x-select.option value="text" label="Question Text" />
                        <x-select.option value="difficulty" label="Difficulty" />
                        <x-select.option value="marks" label="Marks" />
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
    <x-modal wire:model="exportModal" title="Export Questions">
        <div class="mb-4">
            <x-label for="exportFormat" value="Select Format" />
            <x-select wire:model="exportFormat">
                <x-select.option value="pdf" label="PDF" />
                <x-select.option value="xlsx" label="Excel (XLSX)" />
                <x-select.option value="csv" label="CSV" />
            </x-select>
            
            <p class="text-sm text-gray-500 mt-2">
                Note: The export will include current filters and sorting.
            </p>
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.exportModal = false" />
            <x-button label="Export" wire:click="export" spinner class="btn-primary" />
        </x-slot:actions>
    </x-modal>
    
    <!-- IMPORT MODAL -->
    <x-modal wire:model="importModal" title="Import Questions">
        <form wire:submit="import">
            <div class="mb-4">
                <x-label for="importFile" value="Upload File" />
                <input type="file" wire:model="importFile" class="w-full border border-gray-300 rounded p-2" accept=".xlsx,.csv" />
                @error('importFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                
                <p class="text-sm text-gray-500 mt-4">
                    <strong>Accepted formats:</strong> CSV, XLSX<br>
                    <strong>Max file size:</strong> 10MB
                </p>
                
                <p class="text-sm mt-4">
                    <a href="{{ route('questions.download-template') }}" class="text-blue-600 hover:underline">
                        Download import template
                    </a>
                </p>
            </div>
        
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.importModal = false" />
                <x-button type="submit" label="Import" spinner class="btn-primary" />
            </x-slot:actions>
        </form>
    </x-modal>
    
    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="Question Details" size="2xl">
        @if($viewingQuestion)
            <div class="space-y-6">
                <!-- Question text -->
                <div>
                    <h3 class="text-lg font-medium mb-2">Question</h3>
                    <div class="bg-gray-50 p-3 rounded-md">
                        {!! $viewingQuestion->text !!}
                    </div>
                </div>
                
                <!-- Question metadata -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Type</p>
                        <p class="font-medium">{{ $viewingQuestion->questionType->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Subject</p>
                        <p class="font-medium">{{ $viewingQuestion->subject->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Difficulty</p>
                        <x-badge :value="ucfirst($viewingQuestion->difficulty)" 
                                :color="$viewingQuestion->difficulty === 'easy' ? 'success' : ($viewingQuestion->difficulty === 'medium' ? 'warning' : 'error')" />
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Marks</p>
                        <p class="font-medium">{{ $viewingQuestion->marks }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <x-badge :value="$viewingQuestion->status" 
                                :color="$viewingQuestion->status === 'active' ? 'success' : 'warning'" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Used in Papers</p>
                        <p class="font-medium">{{ $viewingQuestion->papers_count }}</p>
                    </div>
                </div>
                
                <!-- Answer options -->
                @if($viewingQuestion->options && $viewingQuestion->options->count() > 0)
                    <div>
                        <h3 class="text-lg font-medium mb-2">Answer Options</h3>
                        <div class="space-y-2">
                            @foreach($viewingQuestion->options as $option)
                                <div class="flex items-start p-2 {{ $option->is_correct ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }} rounded-md">
                                    <div class="mr-3 flex-shrink-0 mt-0.5">
                                        @if($option->is_correct)
                                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <span class="inline-block h-5 w-5 rounded-full bg-gray-200"></span>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        {!! $option->text !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Papers using this question -->
                @if($viewingQuestion->papers && $viewingQuestion->papers->count() > 0)
                    <div>
                        <h3 class="text-lg font-medium mb-2">Used in Papers</h3>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($viewingQuestion->papers as $paper)
                                <li>{{ $paper->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
        
        <x-slot:actions>
            <x-button label="Close" @click="$wire.viewModal = false" />
            <x-button label="Edit" href="{{ route('questions.edit', $viewingId) }}" class="btn-primary" />
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
                        <h1 class="text-center text-xl font-bold mb-4">Questions List</h1>
                        ${printContents}
                    </div>
                `;
                
                window.print();
                document.body.innerHTML = originalContents;
                @this.dispatch('livewire:initialized');
            });
            
            @this.on('showErrorLogDownload', () => {
                Swal.fire({
                    title: 'Import Completed with Errors',
                    text: 'Some records could not be imported. Would you like to download the error log?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Download Error Log',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("questions.download-error-log") }}';
                    }
                });
            });
        });
    </script>
</div>
