<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionTypeFormRequest;
use App\Http\Resources\QuestionTypeResource;
use App\Services\QuestionTypeService;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuestionTypeController extends Controller
{
    protected $questionTypeService;

    public function __construct(QuestionTypeService $questionTypeService)
    {
        $this->questionTypeService = $questionTypeService;
        $this->middleware('permission:view-question-types')->only(['index', 'show', 'search']);
        $this->middleware('permission:create-question-types')->only(['create', 'store']);
        $this->middleware('permission:edit-question-types')->only(['edit', 'update']);
        $this->middleware('permission:delete-question-types')->only('destroy');
    }

    /**
     * Display a listing of question types with caching.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Create cache key for paginated results
        $cacheKey = 'question_types:list:' . ($request->page ?? 1);
        
        $questionTypes = Cache::remember($cacheKey, now()->addMinutes(30), function() {
            return $this->questionTypeService->getPaginatedQuestionTypes();
        });
        
        // Get usage statistics for each question type
        $stats = Cache::remember('question_types:stats', now()->addHours(1), function() use ($questionTypes) {
            $result = [];
            foreach ($questionTypes as $type) {
                $result[$type->id] = [
                    'questions_count' => $type->questions()->count(),
                    'usage_percentage' => QuestionType::count() > 0
                        ? round(($type->questions()->count() / QuestionType::withCount('questions')->get()->sum('questions_count')) * 100, 1)
                        : 0
                ];
            }
            return $result;
        });
        
        if ($request->expectsJson()) {
            return QuestionTypeResource::collection($questionTypes)
                ->additional(['stats' => $stats]);
        }
        
        return view('question_types.index', compact('questionTypes', 'stats'));
    }

    /**
     * Show the form for creating a new question type.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('question_types.create');
    }

    /**
     * Store a newly created question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(QuestionTypeFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $questionType = $this->questionTypeService->createQuestionType($validatedData);
            
            // Clear relevant caches
            $this->clearQuestionTypeCaches();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question Type created successfully!',
                    'data' => new QuestionTypeResource($questionType)
                ], 201);
            }
            
            return redirect()->route('question-types.index')
                ->with('success', 'Question Type created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create question type', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating question type: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating question type: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified question type with its questions.
     *
     * @param  QuestionType  $questionType
     * @param  Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(QuestionType $questionType, Request $request)
    {
        // Cache question type with related data
        $cacheKey = "question_type:{$questionType->id}:details";
        
        $questionType = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($questionType) {
            return $questionType->load([
                'questions' => function($query) {
                    $query->latest()->take(10);
                }
            ]);
        });
        
        // Get detailed statistics 
        $stats = Cache::remember("question_type:{$questionType->id}:stats", now()->addMinutes(30), function() use ($questionType) {
            return [
                'total_questions' => $questionType->questions()->count(),
                'difficulty_levels' => [
                    'easy' => $questionType->questions()->where('difficulty_level', 'easy')->count(),
                    'medium' => $questionType->questions()->where('difficulty_level', 'medium')->count(),
                    'hard' => $questionType->questions()->where('difficulty_level', 'hard')->count(),
                ],
                'subjects' => $questionType->questions()
                    ->select('subject_id')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('subject_id')
                    ->with('subject:id,name')
                    ->get()
                    ->pluck('count', 'subject.name')
                    ->toArray()
            ];
        });
        
        if ($request->expectsJson()) {
            return (new QuestionTypeResource($questionType))
                ->additional(['stats' => $stats]);
        }
        
        return view('question_types.show', compact('questionType', 'stats'));
    }

    /**
     * Show the form for editing the specified question type.
     *
     * @param  QuestionType  $questionType
     * @return \Illuminate\View\View
     */
    public function edit(QuestionType $questionType)
    {
        return view('question_types.edit', compact('questionType'));
    }

    /**
     * Update the specified question type in storage.
     *
     * @param  QuestionTypeFormRequest  $request
     * @param  QuestionType  $questionType
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(QuestionTypeFormRequest $request, QuestionType $questionType)
    {
        $validatedData = $request->validated();
        
        try {
            $this->questionTypeService->updateQuestionType($questionType, $validatedData);
            
            // Clear relevant caches
            $this->clearQuestionTypeCaches($questionType->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question Type updated successfully!',
                    'data' => new QuestionTypeResource($questionType->fresh())
                ]);
            }
            
            return redirect()->route('question-types.index')
                ->with('success', 'Question Type updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update question type', [
                'id' => $questionType->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating question type: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating question type: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question type from storage with checks.
     *
     * @param  QuestionType  $questionType
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(QuestionType $questionType, Request $request)
    {
        try {
            // Check if deletion is allowed (no questions of this type)
            if ($questionType->questions()->exists()) {
                throw new \Exception('Cannot delete question type that has questions. Please delete or reassign the questions first.');
            }
            
            $this->questionTypeService->deleteQuestionType($questionType);
            
            // Clear relevant caches
            $this->clearQuestionTypeCaches($questionType->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question Type deleted successfully!'
                ]);
            }
            
            return redirect()->route('question-types.index')
                ->with('success', 'Question Type deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete question type', [
                'id' => $questionType->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting question type: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting question type: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of question types matching the search term.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->validate([
            'search' => 'required|string|max:100'
        ])['search'];
        
        $questionTypes = $this->questionTypeService->searchQuestionTypes($searchTerm);
        
        if ($request->expectsJson()) {
            return QuestionTypeResource::collection($questionTypes);
        }
        
        return view('question_types.index', compact('questionTypes', 'searchTerm'));
    }
    
    /**
     * Get the questions for a specific question type.
     *
     * @param  QuestionType  $questionType
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function questions(QuestionType $questionType, Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $cacheKey = "question_type:{$questionType->id}:questions:{$perPage}:" . ($request->page ?? 1);
        
        $questions = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($questionType, $perPage) {
            return $questionType->questions()
                ->with('subject')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $questions
            ]);
        }
        
        return view('question_types.questions', compact('questionType', 'questions'));
    }
    
    /**
     * Clear question type related caches.
     *
     * @param int|null $questionTypeId
     * @return void
     */
    protected function clearQuestionTypeCaches($questionTypeId = null)
    {
        Cache::forget('question_types:list');
        Cache::forget('question_types:stats');
        
        if ($questionTypeId) {
            Cache::forget("question_type:{$questionTypeId}:details");
            Cache::forget("question_type:{$questionTypeId}:stats");
            // Clear cached questions pages for this type
            for ($i = 1; $i <= 5; $i++) {
                Cache::forget("question_type:{$questionTypeId}:questions:15:{$i}");
            }
        }
    }
}