<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SubjectService extends BaseService
{
    /**
     * SubjectService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Subject::class;
    }
    
    /**
     * Get all subjects.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Subject>
     */
    public function getAllSubjects(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated subjects with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Subject>
     */
    public function getPaginatedSubjects(array $filters = [], int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        $query = Subject::query()->with($relations);
        
        // Apply filters
        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->whereHas('departments', function ($q) use ($filters) {
                $q->where('departments.id', $filters['department_id']);
            });
        }
        
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        // Apply search
        if (isset($filters['search']) && $filters['search']) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Apply sorting
        if (isset($filters['sort_by']) && $filters['sort_by']) {
            $direction = $filters['sort_dir'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        return $query->paginate($perPage, $columns);
    }
    
    /**
     * Export subjects based on filters.
     *
     * @param string $format
     * @param array $filters
     * @return string Path to exported file
     */
    public function exportSubjects(string $format, array $filters = []): string
    {
        // Get subjects for export based on filters
        $subjects = $this->getSubjectsForExport($filters);
        
        $fileName = 'subjects_export_' . now()->format('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'csv':
            case 'xlsx':
                return $this->exportToSpreadsheet($subjects, $format, $fileName);
            
            case 'pdf':
                return $this->exportToPdf($subjects, $fileName);
            
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }
    
    /**
     * Get subjects for export.
     *
     * @param array $filters
     * @return Collection<int, Subject>
     */
    protected function getSubjectsForExport(array $filters): Collection
    {
        $query = Subject::query()
            ->with(['departments:id,name']);
        
        // Apply department filter
        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->whereHas('departments', function ($q) use ($filters) {
                $q->where('departments.id', $filters['department_id']);
            });
        }
        
        // Apply status filter
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        // Apply search
        if (isset($filters['search']) && $filters['search']) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Apply sorting
        if (isset($filters['sort_by']) && $filters['sort_by']) {
            $direction = $filters['sort_dir'] ?? 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        return $query->get();
    }
    
    /**
     * Export subjects to spreadsheet (CSV or XLSX).
     *
     * @param Collection $subjects
     * @param string $format
     * @param string $fileName
     * @return string Path to exported file
     */
    protected function exportToSpreadsheet(Collection $subjects, string $format, string $fileName): string
    {
        $filePath = "exports/{$fileName}.{$format}";
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        $writer = SimpleExcelWriter::create($fullPath);
        
        $exportData = $subjects->map(function ($subject) {
            return [
                'ID' => $subject->id,
                'Name' => $subject->name,
                'Code' => $subject->code,
                'Description' => $subject->description,
                'Status' => $subject->status,
                'Departments' => $subject->departments->pluck('name')->implode(', '),
                'Created At' => $subject->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        $writer->addRows($exportData);
        
        return Storage::url($filePath);
    }
    
    /**
     * Export subjects to PDF.
     *
     * @param Collection $subjects
     * @param string $fileName
     * @return string Path to exported file
     */
    protected function exportToPdf(Collection $subjects, string $fileName): string
    {
        $filePath = "exports/{$fileName}.pdf";
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        $pdf = PDF::loadView('exports.subjects', [
            'subjects' => $subjects,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ]);
        
        $pdf->save($fullPath);
        
        return Storage::url($filePath);
    }

    /**
     * Get a subject by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Subject|null
     */
    public function getSubjectById(int $id, array $columns = ['*'], array $relations = []): ?Subject
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a subject by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Subject
     */
    public function getSubjectByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Subject
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new subject.
     *
     * @param array $data
     * @return Subject
     */
    public function createSubject(array $data): Subject
    {
        return $this->create($data);
    }

    /**
     * Update an existing subject.
     *
     * @param Subject $subject
     * @param array $data
     * @return Subject
     */
    public function updateSubject(Subject $subject, array $data): Subject
    {
        return $this->update($subject, $data);
    }

    /**
     * Delete a subject.
     *
     * @param Subject $subject
     * @return bool|null
     */
    public function deleteSubject(Subject $subject): ?bool
    {
        return $this->delete($subject);
    }

    /**
     * Restore a soft-deleted subject.
     *
     * @param int $id
     * @return bool
     */
    public function restoreSubject(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a subject permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteSubject(int $id): ?bool
    {
        return $this->forceDelete($id);
    }

    /**
     * Search subjects by name or description.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function searchSubjects(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get subjects for a specific department.
     *
     * @param int $departmentId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function getSubjectsByDepartment(int $departmentId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Subject::forDepartment($departmentId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get subjects with departments.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $departmentRelations
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function getSubjectsWithDepartments(int $perPage = 10, array $columns = ['*'], array $departmentRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('departments', $departmentRelations, $perPage, $columns);
    }

    /**
     * Get subjects with faculty members.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $facultyRelations
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function getSubjectsWithFaculty(int $perPage = 10, array $columns = ['*'], array $facultyRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('facultyMembers', $facultyRelations, $perPage, $columns);
    }

    /**
     * Add a department to a subject.
     *
     * @param Subject $subject
     * @param int $departmentId
     * @return void
     */
    public function addDepartmentToSubject(Subject $subject, int $departmentId): void
    {
        $subject->departments()->attach($departmentId);
    }

    /**
     * Remove a department from a subject.
     *
     * @param Subject $subject
     * @param int $departmentId
     * @return void
     */
    public function removeDepartmentFromSubject(Subject $subject, int $departmentId): void
    {
        $subject->departments()->detach($departmentId);
    }

    /**
     * Add a faculty member to a subject.
     *
     * @param Subject $subject
     * @param int $facultyMemberId
     * @return void
     */
    public function addFacultyMemberToSubject(Subject $subject, int $facultyMemberId): void
    {
        $subject->facultyMembers()->attach($facultyMemberId);
    }

    /**
     * Remove a faculty member from a subject.
     *
     * @param Subject $subject
     * @param int $facultyMemberId
     * @return void
     */
    public function removeFacultyMemberFromSubject(Subject $subject, int $facultyMemberId): void
    {
        $subject->facultyMembers()->detach($facultyMemberId);
    }
    
    /**
     * Get subjects by popularity (based on number of papers).
     *
     * @param int $limit
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Subject>
     */
    public function getPopularSubjects(int $limit = 10, array $columns = ['*'], array $relations = []): Collection
    {
        return Subject::withCount('papers')
            ->with($relations)
            ->orderByDesc('papers_count')
            ->limit($limit)
            ->get($columns);
    }
}