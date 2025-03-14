<?php

namespace App\Services;

use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Paper;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * @var TestAttemptService
     */
    protected TestAttemptService $testAttemptService;
    
    /**
     * @var PaperService
     */
    protected PaperService $paperService;
    
    /**
     * @var UserService
     */
    protected UserService $userService;
    
    /**
     * @var SubjectService
     */
    protected SubjectService $subjectService;
    
    /**
     * StatisticsService constructor.
     *
     * @param TestAttemptService $testAttemptService
     * @param PaperService $paperService
     * @param UserService $userService
     * @param SubjectService $subjectService
     */
    public function __construct(
        TestAttemptService $testAttemptService,
        PaperService $paperService,
        UserService $userService,
        SubjectService $subjectService
    ) {
        $this->testAttemptService = $testAttemptService;
        $this->paperService = $paperService;
        $this->userService = $userService;
        $this->subjectService = $subjectService;
    }
    
    /**
     * Get test attempt statistics.
     *
     * @param int|null $days
     * @return array
     */
    public function getTestAttemptStatistics(?int $days = 30): array
    {
        $query = TestAttempt::query();
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        $totalAttempts = $query->count();
        $completedAttempts = (clone $query)->whereNotNull('completed_at')->count();
        $inProgressAttempts = $totalAttempts - $completedAttempts;
        $averageScore = (clone $query)->whereNotNull('completed_at')->avg('score');
        
        return [
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'in_progress_attempts' => $inProgressAttempts,
            'completion_rate' => $totalAttempts > 0 ? round(($completedAttempts / $totalAttempts) * 100, 2) : 0,
            'average_score' => round($averageScore ?? 0, 2),
        ];
    }
    
    /**
     * Get user statistics.
     *
     * @param int|null $days
     * @return array
     */
    public function getUserStatistics(?int $days = 30): array
    {
        $query = User::query();
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        $totalUsers = $query->count();
        $activeUsers = (clone $query)->whereHas('testAttempts', function($query) use ($days) {
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        })->count();
        
        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $totalUsers - $activeUsers,
            'activity_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
        ];
    }
    
    /**
     * Get paper statistics.
     *
     * @param int|null $days
     * @return array
     */
    public function getPaperStatistics(?int $days = 30): array
    {
        $query = Paper::query();
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        $totalPapers = $query->count();
        $publishedPapers = (clone $query)->where('is_published', true)->count();
        $unpublishedPapers = $totalPapers - $publishedPapers;
        
        $popularPapers = Paper::withCount(['testAttempts' => function($query) use ($days) {
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }])
        ->orderByDesc('test_attempts_count')
        ->limit(5)
        ->get(['id', 'title', 'test_attempts_count']);
        
        return [
            'total_papers' => $totalPapers,
            'published_papers' => $publishedPapers,
            'unpublished_papers' => $unpublishedPapers,
            'publication_rate' => $totalPapers > 0 ? round(($publishedPapers / $totalPapers) * 100, 2) : 0,
            'popular_papers' => $popularPapers,
        ];
    }
    
    /**
     * Get subject statistics.
     *
     * @param int|null $days
     * @return array
     */
    public function getSubjectStatistics(?int $days = 30): array
    {
        $query = Subject::query();
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        $totalSubjects = $query->count();
        
        $popularSubjects = Subject::withCount(['papers' => function($query) {
            $query->whereHas('testAttempts');
        }])
        ->orderByDesc('papers_count')
        ->limit(5)
        ->get(['id', 'name', 'papers_count']);
        
        return [
            'total_subjects' => $totalSubjects,
            'popular_subjects' => $popularSubjects,
        ];
    }
    
    /**
     * Get daily test attempt counts for a given period.
     *
     * @param int $days
     * @return array
     */
    public function getDailyTestAttemptCounts(int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        $results = TestAttempt::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        $dateRange = collect();
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateRange->put($date->format('Y-m-d'), 0);
        }
        
        $formattedResults = $results->keyBy('date')->map(function ($item) {
            return $item->count;
        });
        
        return $dateRange->merge($formattedResults)->toArray();
    }
    
    /**
     * Get difficulty level distribution.
     *
     * @return array
     */
    public function getDifficultyLevelDistribution(): array
    {
        return Paper::select('difficulty_level', DB::raw('COUNT(*) as count'))
            ->groupBy('difficulty_level')
            ->orderBy('difficulty_level')
            ->pluck('count', 'difficulty_level')
            ->toArray();
    }
    
    /**
     * Get user performance by category.
     *
     * @return Collection
     */
    public function getUserPerformanceByCategory(): Collection
    {
        return DB::table('user_categories')
            ->select(
                'user_categories.name as category',
                DB::raw('AVG(test_attempts.score) as average_score'),
                DB::raw('COUNT(DISTINCT users.id) as user_count'),
                DB::raw('COUNT(test_attempts.id) as attempt_count')
            )
            ->leftJoin('users', 'users.user_category_id', '=', 'user_categories.id')
            ->leftJoin('test_attempts', 'test_attempts.user_id', '=', 'users.id')
            ->whereNotNull('test_attempts.score')
            ->groupBy('user_categories.id', 'user_categories.name')
            ->orderBy('average_score', 'desc')
            ->get();
    }
}
