<?php

use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionType;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public $question = [
        'text' => '',
        'subject_id' => '',
        'question_type_id' => '',
        'difficulty' => 'medium',
        'marks' => 1,
        'status' => 'active'
    ];
    
    public function save()
    {
        $validated = $this->validate([
            'question.text' => 'required|string',
            'question.subject_id' => 'required|exists:subjects,id',
            'question.question_type_id' => 'required|exists:question_types,id',
            'question.difficulty' => 'required|in:easy,medium,hard',
            'question.marks' => 'required|integer|min:1',
            'question.status' => 'required|in:active,inactive'
        ]);

        Question::create($validated['question']);
        
        $this->success('Question created successfully!');
        return redirect()->route('questions.index');
    }

    public function with(): array
    {
        return [
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'questionTypes' => QuestionType::orderBy('name')->get(['id', 'name']),
            'difficultyLevels' => [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ]
        ];
    }
}; ?>

<div>
    <x-header title="Create Question" separator>
        <x-slot:actions>
            <x-button label="Cancel" href="{{ route('questions.index') }}" responsive icon="o-x-mark" />
            <x-button label="Save" wire:click="save" responsive spinner icon="o-check" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <!-- Question form fields -->
    </x-card>
</div>
