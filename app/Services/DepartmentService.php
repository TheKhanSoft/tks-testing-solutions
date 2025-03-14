<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class DepartmentService extends BaseService
{
    /**
     * DepartmentService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Department::class;
    }
    
    /**
     * Get all departments.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Department>
     */
    public function getAllDepartments(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return $this->create($data);
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
        return $this->update($department, $data);
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool|null
     */
    public function deleteDepartment(Department $department): ?bool
    {
        return $this->delete($department);
    }

    /**
     * Restore a soft-deleted department.
     *
     * @param int $id
     * @return bool
     */
    public function restoreDepartment(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a department permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteDepartment(int $id): ?bool
    {
        return $this->forceDelete($id);
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
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get departments with faculty members.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $facultyRelations
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithFaculty(int $perPage = 10, array $columns = ['*'], array $facultyRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('facultyMembers', $facultyRelations, $perPage, $columns);
    }

    /**
     * Get departments with subjects.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithSubjects(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('subjects', $subjectRelations, $perPage, $columns);
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
    
    /**
     * Get a department by name.
     *
     * @param string $name
     * @param array $columns
     * @param array $relations
     * @return Department|null
     */
    public function getDepartmentByName(string $name, array $columns = ['*'], array $relations = []): ?Department
    {
        return Department::where('name', $name)->with($relations)->first($columns);
    }
    
    /**
     * Get departments with faculty count.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithFacultyCount(?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Department::withCount('facultyMembers')->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
    
    /**
     * Get departments with subject count.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Department>|Collection<int, Department>
     */
    public function getDepartmentsWithSubjectCount(?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Department::withCount('subjects')->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
}