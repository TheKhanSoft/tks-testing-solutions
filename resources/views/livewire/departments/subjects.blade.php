<?php

use App\Models\Department;
use App\Models\Subject;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public Department $department;
    public string $search = '';
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';

    public function mount(Department $department)
    {
        $this->department = $department;
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'code', 'label' => 'Code', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'faculty_members_count', 'label' => 'Faculty', 'sortable' => true],
        ];
    }

    public function with(): array
    {
        return [
            'subjects' => Subject::where('department_id', $this->department->id)
                ->withCount('facultyMembers')
                ->when($this->search, fn($query, $search) => 
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                )
                ->orderBy($this->sort_by, $this->sort_dir)
                ->paginate(10),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <x-header :title="'Subjects - ' . $department->name" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search subjects..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$subjects" sortable wire:loading.class="opacity-50">
            @scope('cell_status', $subject)
                <x-badge :value="$subject->status" 
                         :color="$subject->status === 'active' ? 'success' : 'warning'" />
            @endscope

            @scope('cell_faculty_members_count', $subject)
                {{ $subject->faculty_members_count }}
            @endscope

            @scope('actions', $subject)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $subject->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $subjects->links() }}
        </div>
    </x-card>
</div>
