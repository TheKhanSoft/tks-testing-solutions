<?php

use App\Models\Question;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public Question $question;
    
    public function mount(Question $question)
    {
        $this->question = $question;
    }

    public function delete()
    {
        try {
            $this->question->delete();
            $this->success('Question deleted successfully');
            $this->dispatch('closeModal');
            $this->dispatch('refreshQuestions');
        } catch (\Exception $e) {
            $this->error('Error deleting question: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-modal.header>Delete Question</x-modal.header>

    <div class="p-4">
        <p>Are you sure you want to delete this question? This action cannot be undone.</p>
    </div>

    <x-slot:actions>
        <x-button label="Cancel" @click="$dispatch('closeModal')" />
        <x-button label="Delete" wire:click="delete" class="btn-error" spinner />
    </x-slot:actions>
</div>
