<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacultySubjectFormRequest;
use App\Http\Resources\FacultySubjectResource;
use App\Services\FacultySubjectService;
use App\Models\FacultySubject;
use App\Models\FacultyMember;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FacultySubjectController extends Controller
{
    protected $facultySubjectService;

    public function __construct(FacultySubjectService $facultySubjectService)
    {
        $this->facultySubjectService = $facultySubjectService;
        $this->middleware('permission:view-faculty-subjects')->only(['index', 'show', 'facultySubjects', 'subjectFaculty']);
        $this->middleware('permission:create-faculty-subjects')->only(['create', 'store', 'batchAssign']);
        $this->middleware('permission:edit-faculty-subjects')->only(['edit', 'update']);
        $this->middleware('permission:delete-faculty-subjects')->only('destroy');
    }

    /**
     * Display a listing of faculty subjects with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'faculty_member_id' => 'nullable|exists:faculty_members,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'department_id' => 'nullable|exists:departments,id',
            'expertise_level' => 'nullable|in:beginner,intermediate,expert'
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'faculty_subjects:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $facultySubjects = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($filters) {
            return $this->facultySubjectService->getPaginatedFacultySubjects($filters);
        });
        
        if ($request->expectsJson()) {
            return FacultySubjectResource::collection($facultySubjects);
        }
        
        $facultyMembers = Cache::remember('faculty_members:dropdown', now()->addHours(3), function() {
            return FacultyMember::with('department')->get(['id', 'name', 'department_id']);
        });
        
        $subjects = Cache::remember('subjects:dropdown', now()->addHours(3), function() {
            return Subject::all(['id', 'name', 'code']);
        });
        
        return view('faculty_subjects.index', compact('facultySubjects', 'facultyMembers', 'subjects', 'filters'));
    }

    /**
     * Show the form for creating a new faculty subject.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $facultyMembers = FacultyMember::with('department')->get();
        $subjects = Subject::all();
        
        // If faculty_member_id is provided, pre-select that faculty member
        $selectedFacultyId = $request->input('faculty_member_id');
        $selectedSubjectId = $request->input('subject_id');
        
        return view('faculty_subjects.create', compact('facultyMembers', 'subjects', 'selectedFacultyId', 'selectedSubjectId'));
    }

    /**
     * Store a newly created faculty subject in storage.
     *
     * @param  FacultySubjectFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(FacultySubjectFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $facultySubject = $this->facultySubjectService->createFacultySubject($validatedData);
            
            // Clear relevant caches
            $this->clearFacultySubjectCaches(
                $facultySubject->faculty_member_id,
                $facultySubject->subject_id
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Subject assigned to faculty member successfully!',
                    'data' => new FacultySubjectResource($facultySubject)
                ], 201);
            }
            
            if ($request->has('redirect_to_faculty') && $request->redirect_to_faculty) {
                return redirect()->route('faculty-members.show', $facultySubject->faculty_member_id)
                    ->with('success', 'Subject assigned to faculty member successfully!');
            }
            
            return redirect()->route('faculty-subjects.index')
                ->with('success', 'Subject assigned to faculty member successfully!');
        } catch (\Exception $e) {
            Log::error('Error assigning subject to faculty member', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error assigning subject to faculty member: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error assigning subject to faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified faculty subject with relationships.
     *
     * @param  FacultySubject  $facultySubject
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(FacultySubject $facultySubject, Request $request)
    {
        $cacheKey = "faculty_subject:{$facultySubject->id}";
        
        $facultySubject = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($facultySubject) {
            return $facultySubject->load(['facultyMember.department', 'subject']);
        });
        
        // Get stats about this faculty-subject relationship
        $stats = Cache::remember(
            "faculty_subject:{$facultySubject->id}:stats", 
            now()->addMinutes(30), 
            function() use ($facultySubject) {
                $facultyMember = $facultySubject->facultyMember;
                $subject = $facultySubject->subject;
                
                return [
                    'total_papers' => $facultyMember->createdPapers()
                        ->whereHas('paperSubjects', function($query) use ($subject) {
                            $query->where('subject_id', $subject->id);
                        })
                        ->count(),
                    'total_questions' => $facultyMember->createdQuestions()
                        ->where('subject_id', $subject->id)
                        ->count(),
                    'expertise_level' => $facultySubject->expertise_level ?? 'Not Specified',
                    'years_experience' => $facultySubject->years_experience ?? 'Not Specified'
                ];
            }
        );
        
        if ($request->expectsJson()) {
            return (new FacultySubjectResource($facultySubject))
                ->additional(['stats' => $stats]);
        }
        
        return view('faculty_subjects.show', compact('facultySubject', 'stats'));
    }

    /**
     * Show the form for editing the specified faculty subject.
     *
     * @param  FacultySubject  $facultySubject
     * @return \Illuminate\View\View
     */
    public function edit(FacultySubject $facultySubject)
    {
        $facultyMembers = FacultyMember::with('department')->get();
        $subjects = Subject::all();
        return view('faculty_subjects.edit', compact('facultySubject', 'facultyMembers', 'subjects'));
    }

    /**
     * Update the specified faculty subject in storage.
     *
     * @param  FacultySubjectFormRequest  $request
     * @param  FacultySubject  $facultySubject
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(FacultySubjectFormRequest $request, FacultySubject $facultySubject)
    {
        $validatedData = $request->validated();
        $originalFacultyId = $facultySubject->faculty_member_id;
        $originalSubjectId = $facultySubject->subject_id;
        
        try {
            $this->facultySubjectService->updateFacultySubject($facultySubject, $validatedData);
            
            // Clear relevant caches
            $this->clearFacultySubjectCaches($originalFacultyId, $originalSubjectId);
            if ($originalFacultyId != $facultySubject->faculty_member_id || 
                $originalSubjectId != $facultySubject->subject_id) {
                $this->clearFacultySubjectCaches(
                    $facultySubject->faculty_member_id, 
                    $facultySubject->subject_id
                );
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Faculty Subject updated successfully!',
                    'data' => new FacultySubjectResource($facultySubject->fresh())
                ]);
            }
            
            if ($request->has('redirect_to_faculty') && $request->redirect_to_faculty) {
                return redirect()->route('faculty-members.show', $facultySubject->faculty_member_id)
                    ->with('success', 'Faculty Subject updated successfully!');
            }
            
            return redirect()->route('faculty-subjects.index')
                ->with('success', 'Faculty Subject updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating faculty subject', [
                'id' => $facultySubject->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating faculty subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating faculty subject: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified faculty subject from storage.
     *
     * @param  FacultySubject  $facultySubject
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(FacultySubject $facultySubject, Request $request)
    {
        $facultyId = $facultySubject->faculty_member_id;
        $subjectId = $facultySubject->subject_id;
        
        try {
            $this->facultySubjectService->deleteFacultySubject($facultySubject);
            
            // Clear relevant caches
            $this->clearFacultySubjectCaches($facultyId, $subjectId);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject removed from faculty member successfully!'
                ]);
            }
            
            if ($request->has('redirect_to_faculty') && $request->redirect_to_faculty) {
                return redirect()->route('faculty-members.show', $facultyId)
                    ->with('success', 'Subject removed from faculty member successfully!');
            }
            
            return redirect()->route('faculty-subjects.index')
                ->with('success', 'Subject removed from faculty member successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing subject from faculty member', [
                'id' => $facultySubject->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error removing subject from faculty member: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error removing subject from faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Display subjects for a specific faculty member with expertise data.
     * 
     * @param FacultyMember $facultyMember
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function facultySubjects(FacultyMember $facultyMember, Request $request)
    {
        $cacheKey = "faculty:{$facultyMember->id}:subjects";
        
        $subjects = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($facultyMember) {
            return $this->facultySubjectService->getSubjectsForFaculty($facultyMember);
        });
        
        // Get expertise statistics
        $stats = Cache::remember("faculty:{$facultyMember->id}:expertise-stats", now()->addHours(1), function() use ($subjects) {
            $expertiseCount = [
                'beginner' => 0,
                'intermediate' => 0,
                'expert' => 0,
                'not_specified' => 0,
            ];
            
            foreach ($subjects as $subject) {
                if (!isset($subject->expertise_level) || $subject->expertise_level === null) {
                    $expertiseCount['not_specified']++;
                } else {
                    $expertiseCount[$subject->expertise_level]++;
                }
            }
            
            return [
                'expertise_distribution' => $expertiseCount,
                'total_subjects' => $subjects->count(),
                'average_years_experience' => $subjects->avg('years_experience') ?? 0
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $subjects,
                'stats' => $stats
            ]);
        }
        
        return view('faculty_subjects.faculty_subjects', compact('subjects', 'facultyMember', 'stats'));
    }

    /**
     * Display faculty members for a specific subject with expertise data.
     * 
     * @param Subject $subject
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function subjectFaculty(Subject $subject, Request $request)
    {
        $cacheKey = "subject:{$subject->id}:faculty";
        
        $facultyMembers = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($subject) {
            return $this->facultySubjectService->getFacultyForSubject($subject);
        });
        
        // Get expertise statistics
        $stats = Cache::remember("subject:{$subject->id}:faculty-stats", now()->addHours(1), function() use ($facultyMembers) {
            $expertiseCount = [
                'beginner' => 0,
                'intermediate' => 0,
                'expert' => 0,
                'not_specified' => 0,
            ];
            
            foreach ($facultyMembers as $facultyMember) {
                if (!isset($facultyMember->expertise_level) || $facultyMember->expertise_level === null) {
                    $expertiseCount['not_specified']++;
                } else {
                    $expertiseCount[$facultyMember->expertise_level]++;
                }
            }
            
            return [
                'expertise_distribution' => $expertiseCount,
                'total_faculty' => $facultyMembers->count(),
                'average_years_experience' => $facultyMembers->avg('years_experience') ?? 0
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $facultyMembers,
                'stats' => $stats
            ]);
        }
        
        return view('faculty_subjects.subject_faculty', compact('facultyMembers', 'subject', 'stats'));
    }
    
    /**
     * Batch assign subjects to faculty member with expertise levels.
     * 
     * @param Request $request
     * @param FacultyMember $facultyMember
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function batchAssign(Request $request, FacultyMember $facultyMember)
    {
        $validatedData = $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
            'expertise_level' => 'nullable|in:beginner,intermediate,expert',
            'years_experience' => 'nullable|integer|min:0|max:50'
        ]);
        
        try {
            $count = $this->facultySubjectService->batchAssignSubjects(
                $facultyMember, 
                $validatedData['subject_ids'],
                $validatedData['expertise_level'] ?? null,
                $validatedData['years_experience'] ?? null
            );
            
            // Clear relevant caches
            $this->clearFacultySubjectCaches($facultyMember->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subjects assigned to faculty member successfully!",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('faculty-members.show', $facultyMember)
                ->with('success', "{$count} subjects assigned to faculty member successfully!");
        } catch (\Exception $e) {
            Log::error('Error batch assigning subjects to faculty member', [
                'faculty_member_id' => $facultyMember->id,
                'subject_ids' => $validatedData['subject_ids'],
                'expertise_level' => $validatedData['expertise_level'] ?? null,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error batch assigning subjects: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error batch assigning subjects: ' . $e->getMessage());
        }
    }
    
    /**
     * Update expertise level for faculty-subject relationship.
     * 
     * @param Request $request
     * @param FacultySubject $facultySubject
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updateExpertise(Request $request, FacultySubject $facultySubject)
    {
        $validatedData = $request->validate([
            'expertise_level' => 'required|in:beginner,intermediate,expert',
            'years_experience' => 'nullable|integer|min:0|max:50'
        ]);
        
        try {
            $this->facultySubjectService->updateExpertiseLevel(
                $facultySubject, 
                $validatedData['expertise_level'],
                $validatedData['years_experience'] ?? null
            );
            
            // Clear relevant caches
            $this->clearFacultySubjectCaches(
                $facultySubject->faculty_member_id, 
                $facultySubject->subject_id
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expertise level updated successfully!',
                    'data' => new FacultySubjectResource($facultySubject->fresh())
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Expertise level updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating expertise level', [
                'id' => $facultySubject->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating expertise level: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating expertise level: ' . $e->getMessage());
        }
    }
    
    /**
     * Get faculty expertise report by subject.
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function expertiseReport(Request $request)
    {
        $cacheKey = 'faculty:expertise-report';
        
        $reportData = Cache::remember($cacheKey, now()->addHours(6), function() {
            return $this->facultySubjectService->generateExpertiseReport();
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);
        }
        
        return view('faculty_subjects.expertise_report', compact('reportData'));
    }
    
    /**
     * Clear faculty subject related caches.
     *
     * @param int|null $facultyId
     * @param int|null $subjectId
     * @return void
     */
    protected function clearFacultySubjectCaches($facultyId = null, $subjectId = null)
    {
        Cache::forget('faculty_subjects:list');
        Cache::forget('faculty:expertise-report');
        
        if ($facultyId) {
            Cache::forget("faculty:{$facultyId}:subjects");
            Cache::forget("faculty:{$facultyId}:expertise-stats");
        }
        
        if ($subjectId) {
            Cache::forget("subject:{$subjectId}:faculty");
            Cache::forget("subject:{$subjectId}:faculty-stats");
        }
    }
}
