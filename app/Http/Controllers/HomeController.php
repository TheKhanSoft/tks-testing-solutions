<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Paper;
use App\Models\Question;
use App\Models\Subject;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard with cached statistics.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // System-wide statistics with caching
        $statistics = Cache::remember('dashboard:statistics', now()->addHours(1), function() {
            return [
                'total_questions' => Question::count(),
                'total_papers' => Paper::count(),
                'total_subjects' => Subject::count(),
                'total_candidates' => Candidate::count(),
                'total_test_attempts' => TestAttempt::count(),
                'completed_tests' => TestAttempt::where('status', 'completed')->count(),
                'users' => User::count()
            ];
        });
        
        // Recent activities with lightweight caching
        $recentActivities = Cache::remember('dashboard:recent_activities', now()->addMinutes(5), function() {
            return DB::table('audit_logs')
                ->join('users', 'users.id', '=', 'audit_logs.user_id')
                ->select('audit_logs.*', 'users.name as user_name')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });
        
        // Monthly test statistics
        $monthlyStats = Cache::remember('dashboard:monthly_stats', now()->addHours(3), function() {
            return DB::table('test_attempts')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'statistics' => $statistics,
                'recent_activities' => $recentActivities,
                'monthly_stats' => $monthlyStats
            ]);
        }
        
        return view('home', compact('statistics', 'recentActivities', 'monthlyStats'));
    }
    
    /**
     * Get fresh dashboard statistics (bypass cache).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshStatistics(Request $request)
    {
        // Clear dashboard caches
        Cache::forget('dashboard:statistics');
        Cache::forget('dashboard:recent_activities');
        Cache::forget('dashboard:monthly_stats');
        
        // Get fresh data
        $statistics = [
            'total_questions' => Question::count(),
            'total_papers' => Paper::count(),
            'total_subjects' => Subject::count(),
            'total_candidates' => Candidate::count(),
            'total_test_attempts' => TestAttempt::count(),
            'completed_tests' => TestAttempt::where('status', 'completed')->count(),
            'users' => User::count()
        ];
        
        $recentActivities = DB::table('audit_logs')
            ->join('users', 'users.id', '=', 'audit_logs.user_id')
            ->select('audit_logs.*', 'users.name as user_name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $monthlyStats = DB::table('test_attempts')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
            
        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'recent_activities' => $recentActivities,
            'monthly_stats' => $monthlyStats
        ]);
    }
}
