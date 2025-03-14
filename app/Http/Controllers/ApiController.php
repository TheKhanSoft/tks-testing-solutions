<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Paper;
use App\Models\Question;
use App\Models\Subject;
use App\Models\TestAttempt;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Get basic system information and statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function systemInfo()
    {
        $stats = Cache::remember('api:system_info', now()->addMinutes(30), function() {
            return [
                'total_candidates' => Candidate::count(),
                'total_papers' => Paper::count(),
                'total_questions' => Question::count(),
                'total_subjects' => Subject::count(),
                'test_attempts' => TestAttempt::count(),
                'version' => config('app.version', '1.0.0'),
                'environment' => app()->environment(),
                'server_time' => Carbon::now()->toIso8601String()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get current authenticated user from API token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentUser(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    /**
     * Search across multiple entities (papers, questions, candidates)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->validate(['term' => 'required|string|min:2'])['term'];
        $entities = $request->validate(['entities' => 'nullable|array'])['entities'] ?? ['papers', 'questions', 'candidates', 'subjects'];
        
        $results = [];
        
        if (in_array('papers', $entities)) {
            $results['papers'] = Paper::where('title', 'like', "%{$term}%")
                ->orWhere('paper_code', 'like', "%{$term}%")
                ->limit(10)
                ->get(['id', 'title', 'paper_code']);
        }
        
        if (in_array('questions', $entities)) {
            $results['questions'] = Question::where('text', 'like', "%{$term}%")
                ->limit(10)
                ->get(['id', 'text', 'subject_id']);
        }
        
        if (in_array('candidates', $entities)) {
            $results['candidates'] = Candidate::where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->limit(10)
                ->get(['id', 'name', 'email']);
        }
        
        if (in_array('subjects', $entities)) {
            $results['subjects'] = Subject::where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->limit(10)
                ->get(['id', 'name', 'code']);
        }
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
    
    /**
     * Get all API endpoints and documentation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function endpoints()
    {
        $endpoints = [
            [
                'path' => '/api/system-info',
                'method' => 'GET',
                'description' => 'Get system information and statistics',
                'requires_auth' => true
            ],
            [
                'path' => '/api/current-user',
                'method' => 'GET',
                'description' => 'Get current authenticated user details',
                'requires_auth' => true
            ],
            [
                'path' => '/api/search',
                'method' => 'GET',
                'description' => 'Search across multiple entities',
                'requires_auth' => true,
                'parameters' => [
                    'term' => 'string (required)',
                    'entities' => 'array (optional)'
                ]
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'TKS Testing Solutions API',
                'version' => config('app.version', '1.0.0'),
                'endpoints' => $endpoints
            ]
        ]);
    }
}
