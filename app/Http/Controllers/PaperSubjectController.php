<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperSubjectFormRequest;
use App\Http\Resources\PaperSubjectResource;
use App\Services\PaperSubjectService;
use App\Models\PaperSubject;
use App\Models\Paper;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaperSubjectController extends Controller
{
    protected $paperSubjectService;

    public function __construct(PaperSubjectService $paperSubjectService)
    {
        $this->paperSubjectService = $paperSubjectService;
        $this->middleware('permission:view-paper-subjects')->only(['index', 'show', 'paperSubjects']);
        $this->middleware('permission:create-paper-subjects')->only(['create', 'store', 'batchAssign']);
        $this->middleware('permission:edit-paper-subjects')->only(['edit', 'update']);
        $this->middleware('permission:delete-paper-subjects')->only('destroy');
    }

    /**
     * Display a listing of paper subjects with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'paper_id' => 'nullable|exists:papers,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'paper_subjects:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $paperSubjects = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($filters) {
            return $this->paperSubjectService->getPaginatedPaperSubjects($filters);
        });
        
        if ($request->expectsJson()) {
            return PaperSubjectResource::collection($paperSubjects);
        }
        
        $papers = Cache::remember('papers:dropdown', now()->addHours(3), function() {
            return Paper::all(['id', 'title', 'paper_code']);
        });
        
        $subjects = Cache::remember('subjects:dropdown', now()->addHours(3), function() {
            return Subject::all(['id', 'name', 'code']);
        });
        
        return view('paper_subjects.index', compact('paperSubjects', 'papers', 'subjects', 'filters'));
    }

    /**
     * Show the form for creating a new paper subject.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $papers = Paper::all();
        $subjects = Subject::all();
        
        // If paper_id is provided, pre-select that paper
        $selectedPaperId = $request->input('paper_id');
        
        return view('paper_subjects.create', compact('papers', 'subjects', 'selectedPaperId'));
    }

    /**
     * Store a newly created paper subject in storage.
     *
     * @param  PaperSubjectFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(PaperSubjectFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $paperSubject = $this->paperSubjectService->createPaperSubject($validatedData);
            
            // Clear relevant caches
            $this->clearPaperSubjectCaches($paperSubject->paper_id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Paper Subject added successfully!',
                    'data' => new PaperSubjectResource($paperSubject)
                ], 201);
            }
            
            if ($request->has('redirect_to_paper') && $request->redirect_to_paper) {
                return redirect()->route('papers.show', $paperSubject->paper_id)
                    ->with('success', 'Subject added to paper successfully!');
            }
            
            return redirect()->route('paper-subjects.index')
                ->with('success', 'Paper Subject added successfully!');
        } catch (\Exception $e) {
            Log::error('Error adding subject to paper', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding subject to paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding subject to paper: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified paper subject with relationships.
     *
     * @param  PaperSubject  $paperSubject
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(PaperSubject $paperSubject, Request $request)
    {
        $cacheKey = "paper_subject:{$paperSubject->id}";
        
        $paperSubject = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($paperSubject) {
            return $paperSubject->load(['paper', 'subject']);
        });
        
        if ($request->expectsJson()) {
            return new PaperSubjectResource($paperSubject);
        }
        
        return view('paper_subjects.show', compact('paperSubject'));
    }

    /**
     * Show the form for editing the specified paper subject.
     *
     * @param  PaperSubject  $paperSubject
     * @return \Illuminate\View\View
     */
    public function edit(PaperSubject $paperSubject)
    {
        $papers = Paper::all();
        $subjects = Subject::all();
        return view('paper_subjects.edit', compact('paperSubject', 'papers', 'subjects'));
    }

    /**
     * Update the specified paper subject in storage.
     *
     * @param  PaperSubjectFormRequest  $request
     * @param  PaperSubject  $paperSubject
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(PaperSubjectFormRequest $request, PaperSubject $paperSubject)
    {
        $validatedData = $request->validated();
        $originalPaperId = $paperSubject->paper_id;
        
        try {
            $this->paperSubjectService->updatePaperSubject($paperSubject, $validatedData);
            
            // Clear relevant caches
            $this->clearPaperSubjectCaches($originalPaperId);
            if ($originalPaperId != $paperSubject->paper_id) {
                $this->clearPaperSubjectCaches($paperSubject->paper_id);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paper Subject updated successfully!',
                    'data' => new PaperSubjectResource($paperSubject->fresh())
                ]);
            }
            
            if ($request->has('redirect_to_paper') && $request->redirect_to_paper) {
                return redirect()->route('papers.show', $paperSubject->paper_id)
                    ->with('success', 'Paper Subject updated successfully!');
            }
            
            return redirect()->route('paper-subjects.index')
                ->with('success', 'Paper Subject updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating paper subject', [
                'id' => $paperSubject->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating paper subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating paper subject: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified paper subject from storage.
     *
     * @param  PaperSubject  $paperSubject
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(PaperSubject $paperSubject, Request $request)
    {
        $paperId = $paperSubject->paper_id;
        
        try {
            $this->paperSubjectService->deletePaperSubject($paperSubject);
            
            // Clear relevant caches
            $this->clearPaperSubjectCaches($paperId);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject removed from paper successfully!'
                ]);
            }
            
            if ($request->has('redirect_to_paper') && $request->redirect_to_paper) {
                return redirect()->route('papers.show', $paperId)
                    ->with('success', 'Subject removed from paper successfully!');
            }
            
            return redirect()->route('paper-subjects.index')
                ->with('success', 'Subject removed from paper successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing subject from paper', [
                'id' => $paperSubject->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error removing subject from paper: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error removing subject from paper: ' . $e->getMessage());
        }
    }

    /**
     * Display subjects for a specific paper with distribution statistics.
     * 
     * @param Paper $paper
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function paperSubjects(Paper $paper, Request $request)
    {
        $cacheKey = "paper:{$paper->id}:subjects";
        
        $data = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($paper) {
            $paperSubjects = $this->paperSubjectService->getSubjectsForPaper($paper);
            
            // Add distribution statistics
            $total = $paperSubjects->sum('questions_count');
            foreach ($paperSubjects as $paperSubject) {
                $paperSubject->percentage = $total > 0 
                    ? round(($paperSubject->questions_count / $total) * 100, 1) 
                    : 0;
            }
            
            return [
                'paperSubjects' => $paperSubjects,
                'totalQuestions' => $total
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data['paperSubjects'],
                'totalQuestions' => $data['totalQuestions']
            ]);
        }
        
        return view('paper_subjects.paper_subjects', [
            'paperSubjects' => $data['paperSubjects'], 
            'paper' => $paper,
            'totalQuestions' => $data['totalQuestions']
        ]);
    }

    /**
     * Generate questions based on subject distribution in a paper.
     * 
     * @param Paper $paper
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function generateQuestions(Paper $paper, Request $request)
    {
        try {
            // Validate that the paper has subject distributions
            if ($paper->paperSubjects()->count() === 0) {
                throw new \Exception('Paper must have subjects assigned before generating questions.');
            }
            
            $count = $this->paperSubjectService->generateQuestionsForPaper($paper);
            
            // Clear caches
            $this->clearPaperSubjectCaches($paper->id);
            Cache::forget("paper:{$paper->id}:questions");
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} questions generated for paper based on subject distribution!",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('success', "{$count} questions generated for paper based on subject distribution!");
        } catch (\Exception $e) {
            Log::error('Error generating questions for paper', [
                'paper_id' => $paper->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating questions: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error generating questions: ' . $e->getMessage());
        }
    }
    
    /**
     * Batch assign subjects to a paper with specified quantities.
     *
     * @param Request $request
     * @param Paper $paper
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function batchAssign(Request $request, Paper $paper)
    {
        $validatedData = $request->validate([
            'subjects' => 'required|array',
            'subjects.*.id' => 'required|exists:subjects,id',
            'subjects.*.questions_count' => 'required|integer|min:1',
            'subjects.*.marks' => 'required|integer|min:0',
        ]);
        
        try {
            $count = $this->paperSubjectService->batchAssignSubjects(
                $paper, 
                $validatedData['subjects']
            );
            
            // Clear caches
            $this->clearPaperSubjectCaches($paper->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subjects assigned to paper successfully!",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('papers.show', $paper)
                ->with('success', "{$count} subjects assigned to paper successfully!");
        } catch (\Exception $e) {
            Log::error('Error batch assigning subjects to paper', [
                'paper_id' => $paper->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error assigning subjects: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error assigning subjects: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear paper subject related caches.
     *
     * @param int $paperId
     * @return void
     */
    protected function clearPaperSubjectCaches($paperId = null)
    {
        Cache::forget('paper_subjects:list');
        
        if ($paperId) {
            Cache::forget("paper:{$paperId}:subjects");
            Cache::forget("paper:{$paperId}:details");
        }
    }
}
