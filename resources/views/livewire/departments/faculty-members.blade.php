<?php

use App\Models\Department;
use App\Models\FacultyMember;
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
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'subjects_count', 'label' => 'Subjects', 'sortable' => true],
        ];
    }

    public function with(): array
    {
        return [
            'facultyMembers' => FacultyMember::where('department_id', $this->department->id)
                ->withCount('subjects')
                ->when($this->search, fn($query, $search) => 
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                )
                ->orderBy($this->sort_by, $this->sort_dir)
                ->paginate(10),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <x-header :title="'Faculty Members - ' . $department->name" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search faculty..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$facultyMembers" sortable wire:loading.class="opacity-50">
            @scope('cell_email', $faculty)
                {{ $faculty->email }}
                <p class="text-xs text-gray-500">{{ $faculty->phone ?? 'No phone' }}</p>
            @endscope

            @scope('cell_status', $faculty)
                <x-badge :value="$faculty->status" 
                         :color="$faculty->status === 'active' ? 'success' : 'warning'" />
            @endscope

            @scope('actions', $faculty)
                <div class="flex gap-1">
                    <x-button icon="o-eye" 
                            href="{{ route('faculty.subjects', $faculty->id) }}" 
                            class="btn-ghost btn-sm" 
                            title="View Subjects" />
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $facultyMembers->links() }}
        </div>
    </x-card>
</div>
