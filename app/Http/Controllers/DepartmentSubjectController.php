<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentSubjectFormRequest;
use App\Http\Resources\DepartmentSubjectResource;
use App\Services\DepartmentSubjectService;
use App\Models\DepartmentSubject;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DepartmentSubjectController extends Controller
{
    protected $departmentSubjectService;

    public function __construct(DepartmentSubjectService $departmentSubjectService)
    {
        $this->departmentSubjectService = $departmentSubjectService;
        $this->middleware('permission:view-department-subjects')->only(['index', 'show', 'departmentSubjects', 'subjectDepartments']);
        $this->middleware('permission:create-department-subjects')->only(['create', 'store', 'batchAssign']);
        $this->middleware('permission:edit-department-subjects')->only(['edit', 'update']);
        $this->middleware('permission:delete-department-subjects')->only('destroy');
    }

    /**
     * Display a listing of department subjects with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);
        
        // Generate cache key based on filters
        $cacheKey = 'department_subjects:list:' . md5(json_encode($filters) . $request->page ?? 1);
        
        $departmentSubjects = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($filters) {
            return $this->departmentSubjectService->getPaginatedDepartmentSubjects($filters);
        });
        
        if ($request->expectsJson()) {
            return DepartmentSubjectResource::collection($departmentSubjects);
        }
        
        $departments = Cache::remember('departments:dropdown', now()->addHours(6), function() {
            return Department::all(['id', 'name']);
        });
        
        $subjects = Cache::remember('subjects:dropdown', now()->addHours(6), function() {
            return Subject::all(['id', 'name', 'code']);
        });
        
        return view('department_subjects.index', compact('departmentSubjects', 'departments', 'subjects', 'filters'));
    }

    /**
     * Show the form for creating a new department subject.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $departments = Department::all();
        $subjects = Subject::all();
        
        // If department_id is provided, pre-select that department
        $selectedDepartmentId = $request->input('department_id');
        
        return view('department_subjects.create', compact('departments', 'subjects', 'selectedDepartmentId'));
    }

    /**
     * Store a newly created department subject in storage.
     *
     * @param  DepartmentSubjectFormRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(DepartmentSubjectFormRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $departmentSubject = $this->departmentSubjectService->createDepartmentSubject($validatedData);
            
            // Clear relevant caches
            $this->clearDepartmentSubjectCaches(
                $departmentSubject->department_id, 
                $departmentSubject->subject_id
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject assigned to department successfully!',
                    'data' => new DepartmentSubjectResource($departmentSubject)
                ], 201);
            }
            
            if ($request->has('redirect_to_department') && $request->redirect_to_department) {
                return redirect()->route('departments.show', $departmentSubject->department_id)
                    ->with('success', 'Subject assigned to department successfully!');
            }
            
            return redirect()->route('department-subjects.index')
                ->with('success', 'Subject assigned to department successfully!');
        } catch (\Exception $e) {
            Log::error('Error assigning subject to department', [
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error assigning subject to department: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error assigning subject to department: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified department subject with relationships.
     *
     * @param  DepartmentSubject  $departmentSubject
     * @param  Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(DepartmentSubject $departmentSubject, Request $request)
    {
        $cacheKey = "department_subject:{$departmentSubject->id}";
        
        $departmentSubject = Cache::remember($cacheKey, now()->addMinutes(30), function() use ($departmentSubject) {
            return $departmentSubject->load(['department', 'subject']);
        });
        
        // Get stats about questions for this subject in this department
        $stats = Cache::remember(
            "department_subject:{$departmentSubject->id}:stats", 
            now()->addMinutes(30), 
            function() use ($departmentSubject) {
                return [
                    'total_questions' => $departmentSubject->subject
                        ->questions()
                        ->count(),
                    'faculty_count' => $departmentSubject->department
                        ->facultyMembers()
                        ->whereHas('subjects', function($query) use ($departmentSubject) {
                            $query->where('subject_id', $departmentSubject->subject_id);
                        })
                        ->count()
                ];
            }
        );
        
        if ($request->expectsJson()) {
            return (new DepartmentSubjectResource($departmentSubject))
                ->additional(['stats' => $stats]);
        }
        
        return view('department_subjects.show', compact('departmentSubject', 'stats'));
    }

    /**
     * Show the form for editing the specified department subject.
     *
     * @param  DepartmentSubject  $departmentSubject
     * @return \Illuminate\View\View
     */
    public function edit(DepartmentSubject $departmentSubject)
    {
        $departments = Department::all();
        $subjects = Subject::all();
        return view('department_subjects.edit', compact('departmentSubject', 'departments', 'subjects'));
    }

    /**
     * Update the specified department subject in storage.
     *
     * @param  DepartmentSubjectFormRequest  $request
     * @param  DepartmentSubject  $departmentSubject
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(DepartmentSubjectFormRequest $request, DepartmentSubject $departmentSubject)
    {
        $validatedData = $request->validated();
        $originalDeptId = $departmentSubject->department_id;
        $originalSubjId = $departmentSubject->subject_id;
        
        try {
            $this->departmentSubjectService->updateDepartmentSubject($departmentSubject, $validatedData);
            
            // Clear relevant caches for both old and new values if they changed
            $this->clearDepartmentSubjectCaches($originalDeptId, $originalSubjId);
            if ($originalDeptId != $departmentSubject->department_id || 
                $originalSubjId != $departmentSubject->subject_id) {
                $this->clearDepartmentSubjectCaches(
                    $departmentSubject->department_id, 
                    $departmentSubject->subject_id
                );
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department Subject updated successfully!',
                    'data' => new DepartmentSubjectResource($departmentSubject->fresh())
                ]);
            }
            
            return redirect()->route('department-subjects.index')
                ->with('success', 'Department Subject updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating department subject', [
                'id' => $departmentSubject->id,
                'data' => $validatedData,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating department subject: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating department subject: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified department subject from storage.
     *
     * @param  DepartmentSubject  $departmentSubject
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(DepartmentSubject $departmentSubject, Request $request)
    {
        $deptId = $departmentSubject->department_id;
        $subjId = $departmentSubject->subject_id;
        
        try {
            $this->departmentSubjectService->deleteDepartmentSubject($departmentSubject);
            
            // Clear relevant caches
            $this->clearDepartmentSubjectCaches($deptId, $subjId);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject removed from department successfully!'
                ]);
            }
            
            if ($request->has('redirect_to_department') && $request->redirect_to_department) {
                return redirect()->route('departments.show', $deptId)
                    ->with('success', 'Subject removed from department successfully!');
            }
            
            return redirect()->route('department-subjects.index')
                ->with('success', 'Subject removed from department successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing subject from department', [
                'id' => $departmentSubject->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error removing subject from department: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error removing subject from department: ' . $e->getMessage());
        }
    }

    /**
     * Display subjects for a specific department with statistics.
     * 
     * @param Department $department
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function departmentSubjects(Department $department, Request $request)
    {
        $cacheKey = "department:{$department->id}:subjects";
        
        $subjects = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($department) {
            return $this->departmentSubjectService->getSubjectsForDepartment($department);
        });
        
        // Get statistics about subjects in this department
        $stats = Cache::remember("department:{$department->id}:subject-stats", now()->addHours(1), function() use ($subjects) {
            return [
                'total_subjects' => $subjects->count(),
                'total_questions' => $subjects->sum('questions_count'),
                'subjects_with_faculty' => $subjects->filter(function($subject) {
                    return $subject->faculty_count > 0;
                })->count()
            ];
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $subjects,
                'stats' => $stats
            ]);
        }
        
        return view('department_subjects.department_subjects', compact('subjects', 'department', 'stats'));
    }

    /**
     * Display departments for a specific subject.
     * 
     * @param Subject $subject
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function subjectDepartments(Subject $subject, Request $request)
    {
        $cacheKey = "subject:{$subject->id}:departments";
        
        $departments = Cache::remember($cacheKey, now()->addMinutes(15), function() use ($subject) {
            return $this->departmentSubjectService->getDepartmentsForSubject($subject);
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        }
        
        return view('department_subjects.subject_departments', compact('departments', 'subject'));
    }
    
    /**
     * Batch assign subjects to a department.
     *
     * @param Request $request
     * @param Department $department
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function batchAssign(Request $request, Department $department)
    {
        $validatedData = $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);
        
        try {
            $count = $this->departmentSubjectService->batchAssignSubjects(
                $department, 
                $validatedData['subject_ids']
            );
            
            // Clear relevant caches
            $this->clearDepartmentSubjectCaches($department->id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} subjects assigned to department successfully!",
                    'count' => $count
                ]);
            }
            
            return redirect()->route('departments.show', $department)
                ->with('success', "{$count} subjects assigned to department successfully!");
        } catch (\Exception $e) {
            Log::error('Error batch assigning subjects to department', [
                'department_id' => $department->id,
                'subject_ids' => $validatedData['subject_ids'],
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
     * Clear department subject related caches.
     *
     * @param int|null $departmentId
     * @param int|null $subjectId
     * @return void
     */
    protected function clearDepartmentSubjectCaches($departmentId = null, $subjectId = null)
    {
        Cache::forget('department_subjects:list');
        
        if ($departmentId) {
            Cache::forget("department:{$departmentId}:subjects");
            Cache::forget("department:{$departmentId}:subject-stats");
        }
        
        if ($subjectId) {
            Cache::forget("subject:{$subjectId}:departments");
        }
    }
}
