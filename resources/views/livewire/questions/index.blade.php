<?php

use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use App\Services\QuestionService;
use App\Http\Requests\QuestionFormRequest;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination, WithFileUploads, WithoutUrlPagination;

    public string $search = '';
    public ?string $difficulty_level = null;
    public $subject_id = null;
    public $question_type_id = null;
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
        'change_difficulty_level' => 'Change Difficulty',
        'change_subject' => 'Change Subject',
        'change_type' => 'Change Type',
        'delete' => 'Delete'
    ];
    public $selectedBulkAction = null;
    public $bulkSubject = null;
    public $bulkQuestionType = null;
    public $bulkDifficulty = null;
    public bool $createModal = false;
    public bool $editModal = false;
    public $currentQuestion = null;
    public bool $questionModal = false;
    public $options = [];
    public $newOption = ['text' => '', 'is_correct' => false];

    public $id;
    public $text;
    public $marks;
    public $description;
    public $explanation;
    public $image;
    public $max_time_allowed;
    public $negative_marks;
    public $status;
    public bool $isEditing = false;
    protected $tempFilters = [];
    

    protected $queryString = [];  // Remove all query string parameters

    // Add a method to handle URL cleanup
    private function cleanUrl(): void 
    {
        $currentUrl = request()->url();
        if (request()->getQueryString()) {
            $this->dispatch('cleanUrl', ['url' => $currentUrl]);
        }
    }
    
    public function mount() 
    {
        $this->questionService = app(QuestionService::class);
        $this->cleanUrl();
    }
    
    public function clear(): void
    {
        $this->reset(['search', 'difficulty_level', 'subject_id', 'question_type_id', 'sort_by', 'sort_dir']);
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    public function view($id): void
    {
        $this->viewingId = $id;
        $this->viewingQuestion = Question::with(['subject:id,name', 
            'questionType:id,name',
            'options', // Explicitly load options
            'papers:id,title'
        ])
        ->withCount('papers')
        ->findOrFail($id);
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
        $this->questionService = app(QuestionService::class);
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
            $this->bulkdifficulty_level = null;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }
    
    public function export(): void
    {
        try {
            $filters = [
                'difficulty_level' => $this->difficulty_level,
                'subject_id' => $this->subject_id,
                'question_type_id' => $this->question_type_id,
                'search' => $this->search,
                'sort_by' => $this->sort_by,
                'sort_dir' => $this->sort_dir,
            ];
            $this->questionService = app(QuestionService::class);
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
            if (!$this->importFile) {
                throw new \Exception('No file selected');
            }

            $this->validate([
                'importFile' => ['required', 'file', 'mimes:csv,xlsx', 'max:10240'],
            ], [
                'importFile.required' => 'Please select a file to import',
                'importFile.file' => 'The upload must be a valid file',
                'importFile.mimes' => 'The file must be a CSV or Excel file',
                'importFile.max' => 'The file size must not exceed 10MB',
            ]);

            $this->questionService = app(QuestionService::class);
            $result = $this->questionService->importQuestions($this->importFile);
            
            // Ensure all required keys exist with default values
            $result = array_merge([
                'success' => false,
                'count' => 0,
                'processed' => 0,
                'skipped' => 0,
                'initialSuccessCount' => 0,
                'finalSuccessCount' => 0,
                'rowCount' => 0,
                'errors' => [],
                'error_count' => 0,
                'error_log' => [],
            ], $result ?: []);
            
            $this->importModal = false;
            $this->reset('importFile');
            
            if ($result['success']) {
                $this->success(
                    "Import Completed Successfully!", 
                    "<br />Awesome! The Import questions process completed successfully. <br />
                    Total No. of Questions: <strong>{$result['rowCount']}</strong>,<br />
                    Questions Imported: <strong>{$result['processed']}</strong>, <br />
                    Questions Skipped: <strong>{$result['skipped']}</strong>.", 
                    position: 'toast-top');
            } else {
                $this->error(
                    "Import completed with, {$result['processed']} imported, {$result['skipped']} skipped.", 
                    position: 'toast-bottom'
                );
                
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
            ['key' => 'text', 'label' => 'Question', 'sortable' => true],
            ['key' => 'type', 'label' => 'Type', 'sortable' => false],
            ['key' => 'subject', 'label' => 'Subject', 'sortable' => false],
            ['key' => 'difficulty_level', 'label' => 'Difficulty', 'sortable' => true],
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
    
    private function storeCurrentFilters(): void
    {
        $this->tempFilters = [
            'search' => $this->search,
            'difficulty_level' => $this->difficulty_level,
            'subject_id' => $this->subject_id,
            'question_type_id' => $this->question_type_id,
            'sort_by' => $this->sort_by,
            'sort_dir' => $this->sort_dir
        ];
    }

    private function restoreFilters(): void
    {
        if (!empty($this->tempFilters)) {
            $this->search = $this->tempFilters['search'];
            $this->difficulty_level = $this->tempFilters['difficulty_level'];
            $this->subject_id = $this->tempFilters['subject_id'];
            $this->question_type_id = $this->tempFilters['question_type_id'];
            $this->sort_by = $this->tempFilters['sort_by'];
            $this->sort_dir = $this->tempFilters['sort_dir'];
            $this->tempFilters = [];
        }
    }

    private function clearFilters(): void
    {
        $this->reset([
            'search',
            'difficulty_level',
            'subject_id',
            'question_type_id',
            'sort_by',
            'sort_dir'
        ]);
    }

    public function getQuestionsProperty()
    {
        $query = Question::with(['subject:id,name', 'questionType:id,name']);

        if (!$this->isEditing) {
            $query->when($this->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('text', 'like', "%{$search}%");
                });
            })
            ->when($this->difficulty_level, function($query, $difficulty_level) {
                return $query->where('difficulty_level', $difficulty_level);
            })
            ->when($this->subject_id, function($query, $subject_id) {
                return $query->where('subject_id', $subject_id);
            })
            ->when($this->question_type_id, function($query, $question_type_id) {
                return $query->where('question_type_id', $question_type_id);
            });
        }

        return $query->orderBy($this->sort_by, $this->sort_dir)->paginate(15);
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
                'papers:id,title'
            ])
            ->withCount('papers')
            ->find($this->viewingId);
    }
    
  

    public function getFormattedDifficultyLevelsProperty()
    {
        return [
            ['id' => 'easy', 'name' => 'Easy'],
            ['id' => 'medium', 'name' => 'Medium'],
            ['id' => 'hard', 'name' => 'Hard'],
            ['id' => 'very_hard', 'name' => 'Very Hard'],
            ['id' => 'expert', 'name' => 'Expert'],

        ];
    }

    public function getFormattedSubjectsProperty()
    {
        return $this->subjects->map(function($subject) {
            return [
                'id' => (string) $subject->id,
                'name' => $subject->name ?? $subject->title
            ];
        })->toArray();
    }

    public function getFormattedQuestionTypesProperty()
    {
        return $this->questionTypes->map(function($type) {
            return [
                'id' => (string) $type->id,
                'name' => $type->name
            ];
        })->toArray();
    }

    public function with(): array
    {
        return [
            'questions' => $this->questions,
            'subjects' => $this->subjects,
            'questionTypes' => $this->questionTypes,
            'viewingQuestion' => $this->viewingQuestion,
            'formattedDifficultyLevels' => $this->formattedDifficultyLevels,
            'formattedSubjects' => $this->formattedSubjects,
            'formattedQuestionTypes' => $this->formattedQuestionTypes,
            'headers' => $this->headers()
        ];
    }

    public function create()
    {
        $this->isEditing = true;
        $this->cleanUrl();
        $this->storeCurrentFilters();
        $this->clearFilters();
        
        $this->questionService = app(QuestionService::class);
        $this->resetValidation();
        $this->reset(['id', 'text', 'subject_id', 'question_type_id', 'difficulty_level', 
                     'marks', 'status', 'description', 'explanation', 'image', 
                     'max_time_allowed', 'negative_marks', 'options']);
        
        // Set defaults without affecting filters
        $this->marks = 1;
        $this->status = 'active';
        $this->questionModal = true;
    }
    
    public function edit(Question $question)
    {
        $this->isEditing = true;
        $this->cleanUrl();
        $this->storeCurrentFilters();
        $this->clearFilters();
        
        $this->questionService = app(QuestionService::class);
        $this->resetValidation();
        $this->viewModal = false; // Close view modal if it's open
        
        // Reset filter parameters when editing
        $this->difficulty_level = null;
        $this->subject_id = null;
        $this->question_type_id = null;
        
        // First load the question with its options
        $question = $question->load('options');
        
        // Reset question fields first to avoid carrying over data
        $this->reset(['id', 'text', 'subject_id', 'question_type_id', 'difficulty_level', 
                     'marks', 'status', 'description', 'explanation', 'image', 
                     'max_time_allowed', 'negative_marks', 'options']);
        
        // Fill the form with question data
        $this->id = $question->id;
        $this->text = $question->text;
        $this->subject_id = $question->subject_id;
        $this->question_type_id = $question->question_type_id;
        $this->difficulty_level = $question->difficulty_level;
        $this->marks = $question->marks;
        $this->status = $question->status;
        $this->description = $question->description;
        $this->explanation = $question->explanation;
        $this->max_time_allowed = $question->max_time_allowed;
        $this->negative_marks = $question->negative_marks;
        
        // Format options data properly
        $this->options = $question->options->map(function($option) {
            return [
                'id' => $option->id,
                'text' => $option->text,
                'is_correct' => (bool) $option->is_correct
            ];
        })->toArray();
        
        $this->questionModal = true;
    }

    public function saveQuestion()
    {
        $this->questionService = app(QuestionService::class);
        
        $questionRequest = new QuestionFormRequest();
        $rules = collect($questionRequest->rules())->mapWithKeys(function ($rule, $key) {
            return ["{$key}" => $rule];
        })->toArray();
        
        // Ensure options have their IDs when updating
        //dd($this->options);
        
        // Validate using the mapped rules
        $validated = $this->validate($rules, $questionRequest->messages());

        try {
            if (!isset($this->id)) {
                $this->questionService->createQuestion($validated);
                $message = 'Question created successfully!';
            } else {
                $question = $this->questionService->getQuestionById($this->id);
                $this->questionService->updateQuestion($question, $validated);
                $message = 'Question updated successfully!';
            }
            
            $this->questionModal = false;
            $this->reset(['id', 'text', 'subject_id', 'question_type_id', 'difficulty_level', 
                         'marks', 'status', 'description', 'explanation', 'image', 
                         'max_time_allowed', 'negative_marks', 'options', 'questionModal']);
            $this->success($message, position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function shouldShowOptions(): bool
    {
        $this->questionService = app(QuestionService::class);
        return $this->question_type_id && 
               $this->questionService->shouldShowOptions($this->question_type_id);
    }

    public function addOption()
    {
        if (!empty($this->newOption['text'])) {
            if (!isset($this->options)) {
                $this->options = [];
            }
            $this->options[] = [
                'text' => $this->newOption['text'],
                'is_correct' => $this->newOption['is_correct']
            ];
            $this->newOption = ['text' => '', 'is_correct' => false];
        }
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function delete(Question $question): void
    {
        try {
            $this->questionService = app(QuestionService::class);
            $this->questionService->deleteQuestion($question);
            $this->success('Question deleted successfully.', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function updatedDrawer()
    {
        if (!$this->drawer) {
            $this->cleanUrl();
        }
    }

    public function updatedQuestionModal()
    {
        if (!$this->questionModal) {
            $this->isEditing = false;
            $this->cleanUrl();
            $this->restoreFilters();
        }
    }

    public function updatedViewModal()
    {
        if (!$this->viewModal) {
            $this->cleanUrl();
        }
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
            <x-button 
                label="Create Question" 
                wire:click="create" 
                responsive 
                icon="o-plus" 
                class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- BULK ACTIONS BAR (only visible when bulk edit is active) -->
    @if($bulkEditMode)
        <div class="bg-base-200 p-4 rounded-lg mb-4 flex flex-wrap items-center gap-3">
            <div class="font-medium">Bulk Actions:</div>
            <div class="flex-1 flex flex-wrap gap-2 items-center">
                <x-select wire:model="selectedBulkAction" 
                         placeholder="Select Action" 
                         :options="[
                            ['id' => 'mark_as_active', 'name' => 'Mark as Active'],
                            ['id' => 'mark_as_inactive', 'name' => 'Mark as Inactive'],
                            ['id' => 'change_difficulty', 'name' => 'Change Difficulty'],
                            ['id' => 'change_subject', 'name' => 'Change Subject'],
                            ['id' => 'change_type', 'name' => 'Change Type'],
                            ['id' => 'delete', 'name' => 'Delete']
                         ]" 
                         class="max-w-xs" />
                
                @if($selectedBulkAction === 'change_difficulty')
                    <x-select wire:model="bulkDifficulty" 
                             placeholder="Select Difficulty" 
                             :options="$formattedDifficultyLevels" 
                             class="max-w-xs" />
                @endif
                
                @if($selectedBulkAction === 'change_subject')
                    <x-select wire:model="bulkSubject" 
                             placeholder="Select Subject" 
                             :options="$formattedSubjects" 
                             class="max-w-xs" />
                @endif
                
                @if($selectedBulkAction === 'change_type')
                    <x-select wire:model="bulkQuestionType" 
                             placeholder="Select Question Type" 
                             :options="$formattedQuestionTypes" 
                             class="max-w-xs" />
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
        <x-table :headers="$headers" :rows="$questions" sortable wire:loading.class="opacity-50">
            @scope('cell_text', $question)
                <div class="max-w-sm">
                    {{ Str::limit(strip_tags($question->text), 80) }}
                </div>
            @endscope

            @scope('cell_type', $question)
                {{ $question->questionType->name ?? 'N/A' }}
            @endscope

            @scope('cell_subject', $question)
                {{ $question->subject->name ?? 'N/A' }}
            @endscope

            @scope('cell_difficulty_level', $question)
                <x-badge :value="ucfirst($question->difficulty_level)" 
                        :color="$question->difficulty_level === 'easy' ? 'success' : ($question->difficulty_level === 'medium' ? 'warning' : 'error')" />
            @endscope

            @scope('cell_status', $question)
                <x-badge :value="$question->status" 
                        :color="$question->status === 'active' ? 'success' : 'warning'" />
            @endscope

            @if($bulkEditMode)
                @scope('cell_select', $question)
                    <x-checkbox wire:model.live="selectedQuestions" value="{{ $question->id }}" />
                @endscope
            @endif

            @scope('actions', $question)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $question->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                    <x-button icon="o-pencil" 
                        wire:click="edit({{ $question->id }})"
                        spinner
                        class="btn-ghost btn-sm" title="Edit" />
                    <x-button icon="o-trash" 
                        wire:click="delete({{ $question->id }})" 
                        wire:confirm="Are you sure you want to delete this question?"
                        spinner
                        class="btn-ghost btn-sm text-red-500" title="Delete" />
                </div>
            @endscope
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
                <x-select 
                    lable="Difficulty Level"
                    wire:model.live="difficulty_level" 
                    placeholder="Select Difficulty Level"
                    :options="$formattedDifficultyLevels"
                    clearable />
            </div>
            
            <div>
                <x-select 
                    label="Subject"
                    wire:model.live="subject_id" 
                    placeholder="Select Subject" 
                    :options="$formattedSubjects"
                    clearable 
                />
            </div>
            
            <div>
                <x-select 
                    label="Question Type" 
                    wire:model.live="question_type_id" 
                    placeholder="Select Question Type" 
                    :options="$formattedQuestionTypes"
                    clearable 
                />
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" 
                             :options="[
                                ['id' => 'created_at', 'name' => 'Date Created'],
                                ['id' => 'text', 'name' => 'Question Text'],
                                ['id' => 'difficulty_level', 'name' => 'Difficulty Level'],
                                ['id' => 'marks', 'name' => 'Marks']
                             ]" 
                             class="flex-1" />
                    <x-select wire:model.live="sort_dir" 
                             :options="[
                                ['id' => 'asc', 'name' => 'Ascending'],
                                ['id' => 'desc', 'name' => 'Descending']
                             ]" 
                             class="w-1/3" />
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
            <x-select wire:model="exportFormat" 
                     :options="[
                        ['id' => 'PDF', 'name' => 'pdf'],
                        ['id' => 'Excel (XLSX)', 'name' => 'xlsx'],
                        ['id' => 'CSV', 'name' => 'csv']
                     ]" />
            
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
        <form wire:submit.prevent="import">
            <div class="mb-4">
                <x-label for="importFile" value="Upload File" />
                <input type="file" 
                       id="importFile"
                       wire:model.live="importFile" 
                       class="w-full border border-gray-300 rounded p-2" 
                       accept=".xlsx,.csv" />
                @error('importFile') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                @enderror
                
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
                
                <x-button type="submit" 
                         label="Import" 
                         class="btn-primary" 
                         wire:click.prevent="import" 
                         :disabled="!$importFile"
                         spinner />
            </x-slot:actions>
        </form>
    </x-modal>
    
    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="Question Details" size="xl">
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
                        <p class="text-sm text-gray-500">Difficulty Level</p>
                        <x-badge :value="ucfirst($viewingQuestion->difficulty_level)" 
                                :color="$viewingQuestion->difficulty_level === 'easy' ? 'success' : ($viewingQuestion->difficulty_level === 'medium' ? 'warning' : 'error')" />
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
                                <div class="flex items-start p-2 {{ $option->is_correct ? 'bg-green-50 border border-green-200' : 'bg-red-100' }} rounded-md">
                                    <div class="mr-3 flex-shrink-0 mt-0.5">
                                        @if($option->is_correct)
                                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
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
                                <li>{{ $paper->title }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            
            <x-slot:actions>
                <x-button label="Close" @click="$wire.viewModal = false" />
                @if($viewingQuestion)
                    <x-button label="Edit" wire:click="edit({{ $viewingQuestion->id }})" spinner class="btn-primary" />
                @endif
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Create and edit modals with a single modal -->
    <x-modal wire:model="questionModal" title="{{ isset($id) ? 'Edit Question' : 'Add New Question' }}">
        <form wire:submit.prevent="saveQuestion">
            <div class="space-y-6">
                <div>
                    <x-textarea wire:model="text"  icon="o-chat-bubble-bottom-center-text" label="Question Text" placeholder="Enter question text" required />
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <x-select 
                            label="Subject"
                            wire:model="subject_id"
                            :options="$formattedSubjects"
                            placeholder="Select Subject" 
                            required
                        />
                    </div>

                    <div>
                        <x-select label="Question Type" 
                            wire:model="question_type_id"
                            :options="$formattedQuestionTypes"
                            placeholder="Select Question Type"
                            @change="$wire.shouldShowOptions"
                            required
                        />                       
                    </div>

                    <div>
                        <x-select label="Difficulty Level"
                            wire:model="difficulty_level"
                            :options="$formattedDifficultyLevels"
                            required
                        />
                    </div>

                    <div>
                        <x-input type="number" label="Marks" wire:model="marks" min="1" placeholder="Marks" required />
                    </div>

                    <div>
                        <x-select 
                            label="Status"
                            wire:model="status"
                            :options="[
                                ['id' => 'active', 'name' => 'Active'],
                                ['id' => 'inactive', 'name' => 'Inactive']
                            ]"
                            required
                        />
                    </div>
                </div>
                
                <!-- Only show options section for multiple choice questions -->
                @if($this->shouldShowOptions())
                    <!-- Options Section -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <x-label value="Answer Options" />
                            <x-button size="sm" @click="$wire.options = []" icon="o-trash" class="btn-ghost" tooltip-left="Clear All Options" />
                        </div>
                        
                        <!-- Existing Options -->
                        <div class="space-y-2 mb-4">>
                            @foreach($options as $index => $option)
                                <div class="flex gap-2 items-start p-2 bg-gray-50 rounded-md">
                                    <x-checkbox wire:model="options.{{ $index }}.is_correct" />
                                    <div class="flex-1">
                                        <x-textarea wire:model="options.{{ $index }}.text" placeholder="Option text" rows="1" />
                                    </div>
                                    <x-button @click="$wire.removeOption({{ $index }})" icon="o-x-mark" class="btn-ghost btn-sm mt-2" />
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Add New Option -->
                        <div class="flex gap-2 items-start">
                            <x-checkbox wire:model="newOption.is_correct" title="Mark as correct answer" />
                            <div class="flex-1">
                                <x-textarea wire:model="newOption.text" placeholder="Add new option" rows="1" />
                            </div>
                            <x-button @click="$wire.addOption()" icon="o-plus" class="btn-primary btn-sm mt-2" />
                        </div>
                    </div>
                @endif
            </div>
            
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.questionModal = false" />
                <x-button label="{{ isset($id) ? 'Update' : 'Create' }}" type="submit" @click="$wire.saveQuestion()" class="btn-primary" spinner />
            </x-slot:actions>
        </form>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('triggerDownload', (data) => {
            if (!data[0].url) {
                console.error('No URL provided for download');
                return;
            }

            let url = data[0].url;
            let filename = url.split('/').pop();
            
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    console.error('Download failed:', error);
                });
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

        @this.on('refreshQuestions', () => {
            @this.dispatch('$refresh');
        });

        @this.on('cleanUrl', (data) => {
            if (data[0].url) {
                window.history.replaceState(null, '', data[0].url);
            }
        });
    });
</script>