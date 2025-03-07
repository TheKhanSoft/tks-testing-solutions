<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class SubjectService
{
    /**
     * Get all subjects.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Subject>
     */
    public function getAllSubjects(array $columns = ['*'], array $relations = []): Collection
    {
        return Subject::with($relations)->get($columns);
    }

    /**
     * Get paginated subjects.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Subject>
     */
    public function getPaginatedSubjects(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return Subject::with($relations)->paginate($perPage, $columns);
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
        return Subject::with($relations)->find($id, $columns);
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
        return Subject::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new subject.
     *
     * @param array $data
     * @return Subject
     */
    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
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
        $subject->update($data);
        return $subject;
    }

    /**
     * Delete a subject.
     *
     * @param Subject $subject
     * @return bool|null
     */
    public function deleteSubject(Subject $subject): ?bool
    {
        return $subject->delete();
    }

    /**
     * Restore a soft-deleted subject.
     *
     * @param int $id
     * @return bool
     */
    public function restoreSubject(int $id): bool
    {
        return Subject::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a subject permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteSubject(int $id): ?bool
    {
        return Subject::withTrashed()->findOrFail($id)->forceDelete();
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
        $query = Subject::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
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
     * @param array $departmentRelations Relations for departments if eager loading is needed for them as well
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function getSubjectsWithDepartments(int $perPage = 10, array $columns = ['*'], array $departmentRelations = []): Paginator|Collection
    {
        $query = Subject::with(['departments' => function ($query) use ($departmentRelations) {
            if (!empty($departmentRelations)) {
                $query->with($departmentRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get subjects with faculty members.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $facultyRelations Relations for faculty members if eager loading is needed for them as well
     * @return Paginator<Subject>|Collection<int, Subject>
     */
    public function getSubjectsWithFaculty(int $perPage = 10, array $columns = ['*'], array $facultyRelations = []): Paginator|Collection
    {
        $query = Subject::with(['facultyMembers' => function ($query) use ($facultyRelations) {
            if (!empty($facultyRelations)) {
                $query->with($facultyRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
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
}