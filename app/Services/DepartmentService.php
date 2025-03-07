<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class DepartmentService
{
    /**
     * Get all departments.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Department>
     */
    public function getAllDepartments(array $columns = ['*'], array $relations = []): Collection
    {
        return Department::with($relations)->get($columns);
    }

    /**
     * Get paginated departments.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Department>
     */
    public function getPaginatedDepartments(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return Department::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a department by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Department|null
     */
    public function getDepartmentById(int $id, array $columns = ['*'], array $relations = []): ?Department
    {
        return Department::with($relations)->find($id, $columns);
    }

    /**
     * Get a department by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Department
     */
    public function getDepartmentByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Department
    {
        return Department::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Update an existing department.
     *
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool|null
     */
    public function deleteDepartment(Department $department): ?bool
    {
        return $department->delete();
    }

    /**
     * Restore a soft-deleted department.
     *
     * @param int $id
     * @return bool
     */
    public function restoreDepartment(int $id): bool
    {
        return Department::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a department permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteDepartment(int $id): ?bool
    {
        return Department::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search departments by name or description.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function searchDepartments(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Department::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get departments with faculty members.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $facultyRelations Relations for faculty members if eager loading is needed for them as well
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithFaculty(int $perPage = 10, array $columns = ['*'], array $facultyRelations = []): Paginator|Collection
    {
        $query = Department::with(['facultyMembers' => function ($query) use ($facultyRelations) {
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
     * Get departments with subjects.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations Relations for subjects if eager loading is needed for them as well
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithSubjects(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        $query = Department::with(['subjects' => function ($query) use ($subjectRelations) {
            if (!empty($subjectRelations)) {
                $query->with($subjectRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Add a subject to a department.
     *
     * @param Department $department
     * @param int $subjectId
     * @return void
     */
    public function addSubjectToDepartment(Department $department, int $subjectId): void
    {
        $department->subjects()->attach($subjectId);
    }

    /**
     * Remove a subject from a department.
     *
     * @param Department $department
     * @param int $subjectId
     * @return void
     */
    public function removeSubjectFromDepartment(Department $department, int $subjectId): void
    {
        $department->subjects()->detach($subjectId);
    }
}