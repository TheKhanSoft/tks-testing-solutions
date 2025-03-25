<?php

use App\Models\TestAttempt;
use App\Models\Paper;
use App\Models\User;
use App\Services\TestAttemptService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public ?string $completion_status = null;
    public ?int $paper_id = null;
    public ?int $user_id = null;
    public string $sort_by = 'created_at';
    public string $sort_dir = 'desc';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public bool $drawer = false;
    public bool $viewModal = false;
    public ?int $viewingId = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'completion_status' => ['except' => null],
        'paper_id' => ['except' => null],
        'user_id' => ['except' => null],
        'sort_by' => ['except' => 'created_at'],
        'sort_dir' => ['except' => 'desc'],
        'start_date' => ['except' => null],
        'end_date' => ['except' => null],
    ];
    
    public function mount(TestAttemptService $testAttemptService) 
    {
        $this->testAttemptService = $testAttemptService;
    }
    
    // Clear filters
    public function clear(): void
    {
        $this->reset(['search', 'completion_status', 'paper_id', 'user_id', 'sort_by', 'sort_dir', 'start_date', 'end_date']);
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    public function view($id): void
    {
        $this->viewingId = $id;
        $this->viewModal = true;
    }
    
    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'sortable' => true],
            ['key' => 'user', 'label' => 'User', 'sortable' => false],
            ['key' => 'paper', 'label' => 'Paper', 'sortable' => false],
            ['key' => 'score', 'label' => 'Score', 'sortable' => true],
            ['key' => 'start_time', 'label' => 'Started At', 'sortable' => true],
            ['key' => 'completion_status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'time_taken', 'label' => 'Time Taken', 'sortable' => false],
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
    
    public function print()
    {
        $this->dispatch('printTable');
    }
    
    public function getViewingTestAttemptProperty()
    {
        if (!$this->viewingId) {
            return null;
        }
        
        // Eager load related data to avoid N+1 query problems
        return TestAttempt::with([
                'user', 
                'paper.subject',
                'answers' => function($query) {
                    $query->with(['question', 'selectedOptions']);
                }
            ])
            ->find($this->viewingId);
    }
    
    public function getUsersProperty()
    {
        // Optimize by only fetching needed columns
        return User::select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
    
    public function getPapersProperty()
    {
        // Optimize by only fetching needed columns and eager loading subjects
        return Paper::select(['id', 'name', 'subject_id'])
            ->with('subject:id,name')
            ->orderBy('name')
            ->get();
    }
    
    public function getTestAttemptsProperty()
    {
        // Build query with all filters
        $query = TestAttempt::query()
            ->with(['user:id,name', 'paper:id,name,subject_id,total_marks', 'paper.subject:id,name'])
            ->when($this->search, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('paper', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($this->completion_status, function($query, $status) {
                if ($status === 'completed') {
                    $query->whereNotNull('completed_at');
                } elseif ($status === 'in_progress') {
                    $query->whereNull('completed_at');
                }
            })
            ->when($this->paper_id, function($query, $paperId) {
                $query->where('paper_id', $paperId);
            })
            ->when($this->user_id, function($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($this->start_date, function($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($this->end_date, function($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy($this->sort_by, $this->sort_dir);
        
        // Cache the query results for better performance
        return cache()->remember(
            'test_attempts_' . md5(json_encode([
                $this->search,
                $this->completion_status,
                $this->paper_id,
                $this->user_id,
                $this->sort_by,
                $this->sort_dir,
                $this->start_date,
                $this->end_date,
                $this->currentPage()
            ])), 
            now()->addMinutes(5), 
            function() use ($query) {
                return $query->paginate(15);
            }
        );
    }

    public function with(): array
    {
        return [
            'testAttempts' => $this->testAttempts,
            'users' => $this->users,
            'papers' => $this->papers,
            'viewingTestAttempt' => $this->viewingTestAttempt,
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Test Attempts" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search test attempts..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Print" wire:click="print" responsive icon="o-printer" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card id="printable-table">
        <x-table :headers="$headers" :rows="$testAttempts" sortable wire:loading.class="opacity-50">
            @scope('cell_user', $testAttempt)
                {{ $testAttempt->user->name ?? 'Unknown User' }}
            @endscope

            @scope('cell_paper', $testAttempt)
                <div>
                    <p>{{ $testAttempt->paper->name ?? 'Unknown Paper' }}</p>
                    <p class="text-xs text-gray-500">{{ $testAttempt->paper->subject->name ?? '' }}</p>
                </div>
            @endscope

            @scope('cell_score', $testAttempt)
                @if($testAttempt->completed_at)
                    {{ $testAttempt->score ?? 0 }}/{{ $testAttempt->paper->total_marks ?? 0 }}
                    <p class="text-xs {{ $testAttempt->passed ? 'text-green-600' : 'text-red-600' }}">
                        {{ $testAttempt->passed ? 'Passed' : 'Failed' }}
                    </p>
                @else
                    <span class="text-gray-500">In progress</span>
                @endif
            @endscope

            @scope('cell_completion_status', $testAttempt)
                <x-badge :value="$testAttempt->completed_at ? 'Completed' : 'In Progress'" 
                         :color="$testAttempt->completed_at ? 'success' : 'warning'" />
            @endscope

            @scope('cell_time_taken', $testAttempt)
                @if($testAttempt->completed_at)
                    {{ $testAttempt->created_at->diffInMinutes($testAttempt->completed_at) }} min
                @else
                    {{ $testAttempt->created_at->diffInMinutes(now()) }} min (ongoing)
                @endif
            @endscope

            @scope('actions', $testAttempt)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="view({{ $testAttempt->id }})" spinner class="btn-ghost btn-sm" title="View Details" />
                </div>
            @endscope
        </x-table>
        
        <div class="mt-4">
            {{ $testAttempts->links() }}
        </div>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Test Attempts" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            
            <div>
                <x-label for="completion_status" value="Status" />
                <x-select wire:model.live="completion_status" placeholder="Select Status" clearable>
                    <x-select.option value="completed" label="Completed" />
                    <x-select.option value="in_progress" label="In Progress" />
                </x-select>
            </div>
            
            <div>
                <x-label for="user_id" value="User" />
                <x-select wire:model.live="user_id" placeholder="Select User" clearable>
                    @foreach($users as $user)
                        <x-select.option value="{{ $user->id }}" label="{{ $user->name }}" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label for="paper_id" value="Paper" />
                <x-select wire:model.live="paper_id" placeholder="Select Paper" clearable>
                    @foreach($papers as $paper)
                        <x-select.option value="{{ $paper->id }}" label="{{ $paper->name }} ({{ $paper->subject->name ?? 'No Subject' }})" />
                    @endforeach
                </x-select>
            </div>
            
            <div>
                <x-label value="Date Range" />
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <x-input type="date" wire:model.live="start_date" placeholder="Start Date" />
                    </div>
                    <div>
                        <x-input type="date" wire:model.live="end_date" placeholder="End Date" />
                    </div>
                </div>
            </div>
            
            <div>
                <x-label for="sort_by" value="Sort By" />
                <div class="flex gap-2">
                    <x-select wire:model.live="sort_by" class="flex-1">
                        <x-select.option value="created_at" label="Start Time" />
                        <x-select.option value="completed_at" label="Completion Time" />
                        <x-select.option value="score" label="Score" />
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
    
    <!-- VIEW MODAL -->
    <x-modal wire:model="viewModal" title="Test Attempt Details" size="3xl">
        @if($viewingTestAttempt)
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">User</p>
                        <p class="font-medium">{{ $viewingTestAttempt->user->name ?? 'Unknown User' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Paper</p>
                        <p class="font-medium">{{ $viewingTestAttempt->paper->name ?? 'Unknown Paper' }}</p>
                        <p class="text-xs text-gray-500">{{ $viewingTestAttempt->paper->subject->name ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <x-badge :value="$viewingTestAttempt->completed_at ? 'Completed' : 'In Progress'" 
                                 :color="$viewingTestAttempt->completed_at ? 'success' : 'warning'" />
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Started At</p>
                        <p class="font-medium">{{ $viewingTestAttempt->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Completed At</p>
                        <p class="font-medium">
                            {{ $viewingTestAttempt->completed_at ? $viewingTestAttempt->completed_at->format('Y-m-d H:i:s') : 'Not completed yet' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Time Taken</p>
                        <p class="font-medium">
                            @if($viewingTestAttempt->completed_at)
                                {{ $viewingTestAttempt->created_at->diffInMinutes($viewingTestAttempt->completed_at) }} minutes
                            @else
                                {{ $viewingTestAttempt->created_at->diffInMinutes(now()) }} minutes (ongoing)
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($viewingTestAttempt->completed_at)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Score</p>
                        <p class="font-medium text-xl">
                            {{ $viewingTestAttempt->score ?? 0 }}/{{ $viewingTestAttempt->paper->total_marks ?? 0 }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Percentage</p>
                        <p class="font-medium text-xl">
                            @if($viewingTestAttempt->paper && $viewingTestAttempt->paper->total_marks > 0)
                                {{ round(($viewingTestAttempt->score / $viewingTestAttempt->paper->total_marks) * 100, 2) }}%
                            @else
                                0%
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Result</p>
                        <p class="font-medium text-xl {{ $viewingTestAttempt->passed ? 'text-green-600' : 'text-red-600' }}">
                            {{ $viewingTestAttempt->passed ? 'Passed' : 'Failed' }}
                        </p>
                    </div>
                </div>
                @endif
                
                <div>
                    <p class="text-sm text-gray-500 mb-2">Answers</p>
                    <div class="overflow-y-auto max-h-96">
                        @if(count($viewingTestAttempt->answers) > 0)
                            <div class="divide-y divide-gray-200">
                                @foreach($viewingTestAttempt->answers as $index => $answer)
                                    <div class="py-3 {{ $answer->is_correct ? 'bg-green-50' : 'bg-red-50' }}">
                                        <p class="font-medium">Question {{ $index + 1 }}: {{ $answer->question->text ?? 'Question text not available' }}</p>
                                        <div class="ml-4 mt-1">
                                            <p class="text-sm"><span class="text-gray-500">Selected:</span> 
                                                @if(count($answer->selectedOptions) > 0)
                                                    @foreach($answer->selectedOptions as $option)
                                                        <span class="{{ $option->is_correct ? 'text-green-600' : 'text-red-600' }}">{{ $option->text }}</span>
                                                        @if(!$loop->last), @endif
                                                    @endforeach
                                                @else
                                                    No option selected
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500">Correct Answer: 
                                                @if($answer->question && $answer->question->options)
                                                    @php
                                                        $correctOptions = $answer->question->options->where('is_correct', true);
                                                    @endphp
                                                    
                                                    @if($correctOptions->count() > 0)
                                                        @foreach($correctOptions as $correctOption)
                                                            <span class="text-green-600">{{ $correctOption->text }}</span>
                                                            @if(!$loop->last), @endif
                                                        @endforeach
                                                    @else
                                                        No correct option defined
                                                    @endif
                                                @else
                                                    Options not available
                                                @endif
                                            </p>
                                            <p class="text-sm">
                                                <span class="text-gray-500">Marks:</span> 
                                                <span class="{{ $answer->marks_obtained > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $answer->marks_obtained ?? 0 }}/{{ $answer->question->marks ?? 0 }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>No answers recorded yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        <x-slot:actions>
            <x-button label="Close" @click="$wire.viewModal = false" />
            @if($viewingTestAttempt && $viewingTestAttempt->completed_at)
                <x-button label="Download Report" icon="o-document-arrow-down" class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-modal>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('printTable', () => {
                const printContents = document.getElementById('printable-table').innerHTML;
                const originalContents = document.body.innerHTML;
                
                document.body.innerHTML = `
                    <div class="print-container">
                        <h1 class="text-center text-xl font-bold mb-4">Test Attempts List</h1>
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
