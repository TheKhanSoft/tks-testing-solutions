<?php

use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Paper;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    
    public string $dateRange = 'week';
    public array $dateRangeOptions = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        'all' => 'All Time',
    ];
    
    // For charts
    public array $testScoreDistribution = [];
    public array $monthlyTestAttempts = [];
    public array $paperCompletionRates = [];
    public array $subjectPerformance = [];
    
    // For recent activities
    public int $recentActivitiesLimit = 8;
    
    public function mount()
    {
        $this->loadChartData();
    }
    
    public function loadChartData()
    {
        $startDate = match ($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'year' => Carbon::now()->subYear(),
            default => null,
        };
        
        $this->loadTestScoreDistribution($startDate);
        $this->loadMonthlyTestAttempts();
        $this->loadPaperCompletionRates($startDate);
        $this->loadSubjectPerformance($startDate);
    }
    
    protected function loadTestScoreDistribution(?Carbon $startDate)
    {
        $query = TestAttempt::query()
            ->selectRaw('
                CASE
                    WHEN score_percentage < 40 THEN "0-40%"
                    WHEN score_percentage BETWEEN 40 AND 60 THEN "40-60%"
                    WHEN score_percentage BETWEEN 60 AND 80 THEN "60-80%"
                    ELSE "80-100%"
                END as range,
                COUNT(*) as count
            ')
            ->whereNotNull('completed_at')
            ->groupBy('range');
            
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        $results = $query->get();
        
        $this->testScoreDistribution = [
            'labels' => $results->pluck('range')->toArray(),
            'data' => $results->pluck('count')->toArray(),
            'colors' => ['#ff6384', '#ffcd56', '#36a2eb', '#4bc0c0']
        ];
    }
    
    protected function loadMonthlyTestAttempts()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        $attempts = TestAttempt::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
        
        // Fill in missing months
        $labels = [];
        $data = [];
        
        for ($i = 0; $i < 6; $i++) {
            $monthKey = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            array_unshift($labels, $monthLabel);
            array_unshift($data, $attempts[$monthKey] ?? 0);
        }
        
        $this->monthlyTestAttempts = [
            'labels' => $labels,
            'data' => $data,
        ];
    }
    
    protected function loadPaperCompletionRates(?Carbon $startDate)
    {
        $query = Paper::query()
            ->select('id', 'name', 'total_marks')
            ->withCount([
                'testAttempts',
                'testAttempts as completed_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                }
            ])
            ->having('test_attempts_count', '>', 0)
            ->orderBy('test_attempts_count', 'desc')
            ->limit(5);
            
        $topPapers = $query->get();
        
        $labels = [];
        $completionData = [];
        
        foreach ($topPapers as $paper) {
            $labels[] = Str::limit($paper->name, 20);
            
            if ($paper->test_attempts_count > 0) {
                $completionPercentage = round(($paper->completed_count / $paper->test_attempts_count) * 100);
            } else {
                $completionPercentage = 0;
            }
            
            $completionData[] = $completionPercentage;
        }
        
        $this->paperCompletionRates = [
            'labels' => $labels,
            'data' => $completionData,
        ];
    }
    
    protected function loadSubjectPerformance(?Carbon $startDate)
    {
        $query = Subject::query()
            ->select('subjects.name')
            ->join('papers', 'subjects.id', '=', 'papers.subject_id')
            ->join('test_attempts', 'papers.id', '=', 'test_attempts.paper_id')
            ->whereNotNull('test_attempts.completed_at')
            ->groupBy('subjects.id', 'subjects.name')
            ->selectRaw('AVG(test_attempts.score_percentage) as avg_score')
            ->orderBy('avg_score', 'desc')
            ->limit(5);
            
        if ($startDate) {
            $query->where('test_attempts.created_at', '>=', $startDate);
        }
        
        $results = $query->get();
        
        $this->subjectPerformance = [
            'labels' => $results->pluck('name')->map(fn($name) => Str::limit($name, 20))->toArray(),
            'data' => $results->pluck('avg_score')->map(fn($score) => round($score, 1))->toArray(),
        ];
    }
    
    public function updateDateRange()
    {
        $this->loadChartData();
        $this->success('Dashboard data updated!', position: 'toast-bottom');
    }
    
    public function getUserStatsProperty()
    {
        $startDate = match ($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'year' => Carbon::now()->subYear(),
            default => null,
        };
        
        $totalUsers = User::count();
        $newUsers = User::when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count();
        $activeUsers = TestAttempt::when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->distinct('user_id')
            ->count('user_id');
        
        return [
            'total' => $totalUsers,
            'new' => $newUsers,
            'active' => $activeUsers,
        ];
    }
    
    public function getTestStatsProperty()
    {
        $startDate = match ($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'year' => Carbon::now()->subYear(),
            default => null,
        };
        
        $query = TestAttempt::when($startDate, fn($q) => $q->where('created_at', '>=', $startDate));
        
        $total = (clone $query)->count();
        $completed = (clone $query)->whereNotNull('completed_at')->count();
        $passed = (clone $query)->where('passed', true)->count();
        
        $avgScore = (clone $query)
            ->whereNotNull('completed_at')
            ->avg('score_percentage') ?? 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'passed' => $passed,
            'passRate' => $completed > 0 ? round(($passed / $completed) * 100, 1) : 0,
            'avgScore' => round($avgScore, 1),
        ];
    }
    
    public function getContentStatsProperty()
    {
        $papers = Paper::count();
        $questions = Question::count();
        $subjects = Subject::count();
        $activeTests = Paper::where('status', 'published')->count();
        
        return [
            'papers' => $papers,
            'questions' => $questions,
            'subjects' => $subjects,
            'activeTests' => $activeTests,
        ];
    }
    
    public function getRecentTestAttemptsProperty()
    {
        return TestAttempt::with(['user:id,name,email', 'paper:id,name,subject_id', 'paper.subject:id,name'])
            ->latest()
            ->take($this->recentActivitiesLimit)
            ->get();
    }
    
    public function getTopPerformingUsersProperty()
    {
        $startDate = match ($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'year' => Carbon::now()->subYear(),
            default => null,
        };
        
        return User::select('users.id', 'users.name', 'users.email')
            ->selectRaw('COUNT(test_attempts.id) as attempt_count')
            ->selectRaw('AVG(test_attempts.score_percentage) as avg_score')
            ->join('test_attempts', 'users.id', '=', 'test_attempts.user_id')
            ->whereNotNull('test_attempts.completed_at')
            ->when($startDate, fn($q) => $q->where('test_attempts.created_at', '>=', $startDate))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->having('attempt_count', '>', 0)
            ->orderBy('avg_score', 'desc')
            ->limit(5)
            ->get();
    }

    public function with(): array
    {
        return [
            'userStats' => $this->userStats,
            'testStats' => $this->testStats,
            'contentStats' => $this->contentStats,
            'recentTestAttempts' => $this->recentTestAttempts,
            'topPerformingUsers' => $this->topPerformingUsers,
            'dateRangeOptions' => $this->dateRangeOptions,
        ];
    }
}; ?>

<div>
    <x-header title="Dashboard" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <span class="text-sm">Period:</span>
                <x-select wire:model.live="dateRange" class="w-40" wire:change="updateDateRange">
                    @foreach($dateRangeOptions as $value => $label)
                        <x-select.option :value="$value" :label="$label" />
                    @endforeach
                </x-select>
            </div>
        </x-slot:actions>
    </x-header>
    
    <!-- Stats overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card class="bg-gradient-to-r from-primary/10 to-blue-100/30">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-3xl font-bold">{{ $userStats['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $userStats['new'] }} new users in selected period</p>
                </div>
                <div class="bg-primary/20 p-3 rounded-full">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </x-card>
        
        <x-card class="bg-gradient-to-r from-green-100/30 to-green-50/30">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Test Attempts</p>
                    <p class="text-3xl font-bold">{{ $testStats['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $testStats['completed'] }} completed ({{ $testStats['passRate'] }}% passed)</p>
                </div>
                <div class="bg-green-100/50 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </x-card>
        
        <x-card class="bg-gradient-to-r from-purple-100/30 to-purple-50/30">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Average Score</p>
                    <p class="text-3xl font-bold">{{ $testStats['avgScore'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Based on {{ $testStats['completed'] }} completed tests</p>
                </div>
                <div class="bg-purple-100/50 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                </div>
            </div>
        </x-card>
        
        <x-card class="bg-gradient-to-r from-amber-100/30 to-amber-50/30">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Content Summary</p>
                    <p class="text-3xl font-bold">{{ $contentStats['papers'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $contentStats['activeTests'] }} active tests, {{ $contentStats['questions'] }} questions
                    </p>
                </div>
                <div class="bg-amber-100/50 p-3 rounded-full">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </x-card>
    </div>
    
    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-card>
            <h3 class="font-medium text-lg mb-4">Test Score Distribution</h3>
            <div class="h-64" wire:ignore>
                <canvas id="scoreDistributionChart"></canvas>
            </div>
        </x-card>
        
        <x-card>
            <h3 class="font-medium text-lg mb-4">Monthly Test Attempts</h3>
            <div class="h-64" wire:ignore>
                <canvas id="monthlyAttemptsChart"></canvas>
            </div>
        </x-card>
        
        <x-card>
            <h3 class="font-medium text-lg mb-4">Paper Completion Rates (%)</h3>
            <div class="h-64" wire:ignore>
                <canvas id="paperCompletionChart"></canvas>
            </div>
        </x-card>
        
        <x-card>
            <h3 class="font-medium text-lg mb-4">Subject Average Performance (%)</h3>
            <div class="h-64" wire:ignore>
                <canvas id="subjectPerformanceChart"></canvas>
            </div>
        </x-card>
    </div>
    
    <!-- Recent activity and top performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <h3 class="font-medium text-lg mb-4">Recent Test Attempts</h3>
            <div class="overflow-x-auto">
                <x-table :headers="[
                    ['key' => 'user', 'label' => 'User', 'sortable' => false],
                    ['key' => 'paper', 'label' => 'Paper', 'sortable' => false],
                    ['key' => 'date', 'label' => 'Date', 'sortable' => false],
                    ['key' => 'status', 'label' => 'Status', 'sortable' => false]
                ]" :rows="$recentTestAttempts" sortable wire:loading.class="opacity-50">
                    
                    @scope('cell_user', $attempt)
                        {{ $attempt->user->name }}
                    @endscope

                    @scope('cell_paper', $attempt)
                        <div class="whitespace-nowrap max-w-[200px] truncate">
                            {{ $attempt->paper->name }}
                        </div>
                    @endscope

                    @scope('cell_date', $attempt)
                        {{ $attempt->created_at->format('M d, H:i') }}
                    @endscope

                    @scope('cell_status', $attempt)
                        @if($attempt->completed_at)
                            <x-badge :value="$attempt->passed ? 'Passed' : 'Failed'" 
                                    :color="$attempt->passed ? 'success' : 'error'" />
                        @else
                            <x-badge value="In Progress" color="warning" />
                        @endif
                    @endscope
                </x-table>
            </div>
        </x-card>

        <x-card>
            <h3 class="font-medium text-lg mb-4">Top Performing Users</h3>
            <div class="overflow-x-auto">
                <x-table :headers="[
                    ['key' => 'user', 'label' => 'User', 'sortable' => false],
                    ['key' => 'tests', 'label' => 'Tests', 'sortable' => false],
                    ['key' => 'score', 'label' => 'Avg. Score', 'sortable' => false]
                ]" :rows="$topPerformingUsers" sortable wire:loading.class="opacity-50">
                    
                    @scope('cell_user', $user)
                        <div>
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        </div>
                    @endscope

                    @scope('cell_tests', $user)
                        <div class="text-center">{{ $user->attempt_count }}</div>
                    @endscope

                    @scope('cell_score', $user)
                        <div class="text-center font-medium {{ $user->avg_score >= 70 ? 'text-green-600' : ($user->avg_score >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ round($user->avg_score, 1) }}%
                        </div>
                    @endscope
                </x-table>
            </div>
        </x-card>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            function initCharts() {
                // Score Distribution Chart
                const scoreDistributionCtx = document.getElementById('scoreDistributionChart').getContext('2d');
                const scoreData = @js($testScoreDistribution);
                new Chart(scoreDistributionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: scoreData.labels,
                        datasets: [{
                            data: scoreData.data,
                            backgroundColor: scoreData.colors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
                
                // Monthly Test Attempts Chart
                const monthlyAttemptsCtx = document.getElementById('monthlyAttemptsChart').getContext('2d');
                const monthlyData = @js($monthlyTestAttempts);
                new Chart(monthlyAttemptsCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.labels,
                        datasets: [{
                            label: 'Test Attempts',
                            data: monthlyData.data,
                            fill: true,
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
                
                // Paper Completion Rates Chart
                const paperCompletionCtx = document.getElementById('paperCompletionChart').getContext('2d');
                const completionData = @js($paperCompletionRates);
                new Chart(paperCompletionCtx, {
                    type: 'bar',
                    data: {
                        labels: completionData.labels,
                        datasets: [{
                            label: 'Completion Rate (%)',
                            data: completionData.data,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + '%';
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Subject Performance Chart
                const subjectPerformanceCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
                const performanceData = @js($subjectPerformance);
                new Chart(subjectPerformanceCtx, {
                    type: 'bar',
                    data: {
                        labels: performanceData.labels,
                        datasets: [{
                            label: 'Average Score (%)',
                            data: performanceData.data,
                            backgroundColor: 'rgba(139, 92, 246, 0.7)',
                            borderColor: 'rgba(139, 92, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            initCharts();
            
            // Update charts when data changes
            @this.on('chartDataUpdated', () => {
                // Destroy old charts and reinitialize with new data
                Chart.getChart('scoreDistributionChart')?.destroy();
                Chart.getChart('monthlyAttemptsChart')?.destroy();
                Chart.getChart('paperCompletionChart')?.destroy();
                Chart.getChart('subjectPerformanceChart')?.destroy();
                initCharts();
            });
        });
    </script>
</div>
