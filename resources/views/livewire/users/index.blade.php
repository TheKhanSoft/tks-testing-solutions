<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';
    public ?string $role = null;
    public string $sort_by = 'name';
    public string $sort_dir = 'asc';
    public bool $drawer = false;
    public bool $viewModal = false;
    public ?int $viewingId = null;

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'roles', 'label' => 'Roles', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Created At', 'sortable' => true],
        ];
    }

    public function getUsersProperty()
    {
        return User::with(['roles'])
            ->when($this->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy($this->sort_by, $this->sort_dir)
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'users' => $this->users,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Users" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search users..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <!-- TABLE -->
    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$users" sortable wire:loading.class="opacity-50">
            @scope('cell_email', $user)
                <div>
                    <p>{{ $user->email }}</p>
                    <p class="text-xs text-gray-500">{{ $user->phone ?? 'No phone' }}</p>
                </div>
            @endscope

            @scope('cell_roles', $user)
                <div class="flex flex-wrap gap-1">
                    @foreach($user->roles as $role)
                        <x-badge :value="$role->name" />
                    @endforeach
                </div>
            @endscope

            @scope('cell_status', $user)
                <x-badge :value="$user->status" 
                         :color="$user->status === 'active' ? 'success' : 'warning'" />
            @endscope

            @scope('actions', $user)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $user->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                    <x-button icon="o-pencil" wire:click="edit({{ $user->id }})" spinner class="btn-ghost btn-sm" title="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $user->id }})" 
                        wire:confirm="Are you sure you want to delete this user?" 
                        spinner class="btn-ghost btn-sm text-red-500" title="Delete" />
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-card>
</div>


