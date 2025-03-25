<?php

use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use App\Services\QuestionService;
use App\Http\Requests\QuestionRequest;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public ?Question $question = null;
    public $formData = [
        'text' => '',
        'subject_id' => '',
        'question_type_id' => '',
        'difficulty' => 'medium',
        'marks' => 1,
        'status' => 'active'
    ];
    public $options = [];

    public function mount($question = null)
    {
        if ($question) {
            $this->question = $question instanceof Question 
                ? $question 
                : Question::find($question['id']);
                
            if ($this->question) {
                $this->formData = $this->question->only([
                    'text', 'subject_id', 'question_type_id', 
                    'difficulty', 'marks', 'status'
                ]);
                $this->options = $this->question->options()->get()->toArray();
            }
        }
    }
    
    public function save()
    {
        $questionService = app(QuestionService::class);
        
        // Validate using form request
        $validated = $this->validate((new QuestionRequest)->rules());
        
        $data = $validated['formData'];
        if ($this->showOptions()) {
            $data['options'] = $this->options;
        }
        
        try {
            if ($this->question) {
                $questionService->updateQuestion($this->question, $data);
                $message = 'Question updated successfully!';
            } else {
                $questionService->createQuestion($data);
                $message = 'Question created successfully!';
            }
            
            $this->success($message);
            $this->dispatch('closeModal');
            $this->dispatch('refreshQuestions');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    public function showOptions(): bool
    {
        return app(QuestionService::class)->isMultipleChoiceQuestion($this->formData['question_type_id'] ?? null);
    }

    public function with(): array
    {
        return [
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'questionTypes' => QuestionType::orderBy('name')->get(['id', 'name']),
            'difficultyLevels' => [
                ['value' => 'easy', 'label' => 'Easy'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'hard', 'label' => 'Hard']
            ],
            'statusOptions' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive']
            ]
        ];
    }
}; ?>

<div>
    <x-modal.header>
        {{ $question ? 'Edit Question' : 'Create Question' }}
    </x-modal.header>

    <form wire:submit="save" class="space-y-4">
        <div>
            <x-label for="text" value="Question Text" required />
            <x-textarea wire:model="formData.text" placeholder="Enter question text" required />
            @error('formData.text') <x-error>{{ $message }}</x-error> @enderror
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <x-label for="subject_id" value="Subject" required />
                <x-select 
                    wire:model="formData.subject_id"
                    :options="$subjects->map(fn($item) => ['value' => $item->id, 'label' => $item->name])->toArray()"
                    placeholder="Select Subject" 
                    required
                />
                @error('formData.subject_id') <x-error>{{ $message }}</x-error> @enderror
            </div>

            <div>
                <x-label for="question_type_id" value="Question Type" required />
                <x-select 
                    wire:model="formData.question_type_id"
                    :options="$questionTypes->map(fn($item) => ['value' => $item->id, 'label' => $item->name])->toArray()"
                    placeholder="Select Question Type"
                    required
                />
                @error('formData.question_type_id') <x-error>{{ $message }}</x-error> @enderror
            </div>

            <div>
                <x-label for="difficulty" value="Difficulty" required />
                <x-select 
                    wire:model="formData.difficulty"
                    :options="$difficultyLevels"
                    required
                />
                @error('formData.difficulty') <x-error>{{ $message }}</x-error> @enderror
            </div>

            <div>
                <x-label for="marks" value="Marks" required />
                <x-input type="number" wire:model="formData.marks" min="1" required />
                @error('formData.marks') <x-error>{{ $message }}</x-error> @enderror
            </div>

            <div>
                <x-label for="status" value="Status" required />
                <x-select 
                    wire:model="formData.status"
                    :options="$statusOptions"
                    required
                />
                @error('formData.status') <x-error>{{ $message }}</x-error> @enderror
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$dispatch('closeModal')" />
            <x-button label="{{ $question ? 'Update' : 'Create' }}" type="submit" class="btn-primary" spinner />
        </x-slot:actions>
    </form>
</div>
