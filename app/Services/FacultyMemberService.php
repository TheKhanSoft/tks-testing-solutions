<?php

namespace App\Services;

use App\Models\FacultyMember;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;

class FacultyMemberService
{
    /**
     * Get all faculty members.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, FacultyMember>
     */
    public function getAllFacultyMembers(array $columns = ['*'], array $relations = []): Collection
    {
        return FacultyMember::with($relations)->get($columns);
    }

    /**
     * Get paginated faculty members.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<FacultyMember>
     */
    public function getPaginatedFacultyMembers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return FacultyMember::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a faculty member by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return FacultyMember|null
     */
    public function getFacultyMemberById(int $id, array $columns = ['*'], array $relations = []): ?FacultyMember
    {
        return FacultyMember::with($relations)->find($id, $columns);
    }

    /**
     * Get a faculty member by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return FacultyMember
     */
    public function getFacultyMemberByIdOrFail(int $id, array $columns = ['*'], array $relations = []): FacultyMember
    {
        return FacultyMember::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new faculty member.
     *
     * @param array $data
     * @return FacultyMember
     */
    public function createFacultyMember(array $data): FacultyMember
    {
        $data['password'] = Hash::make($data['password']); // Hash password before creating
        return FacultyMember::create($data);
    }

    /**
     * Update an existing faculty member.
     *
     * @param FacultyMember $facultyMember
     * @param array $data
     * @return FacultyMember
     */
    public function updateFacultyMember(FacultyMember $facultyMember, array $data): FacultyMember
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']); // Hash password if updated
        }
        $facultyMember->update($data);
        return $facultyMember;
    }

    /**
     * Delete a faculty member.
     *
     * @param FacultyMember $facultyMember
     * @return bool|null
     */
    public function deleteFacultyMember(FacultyMember $facultyMember): ?bool
    {
        return $facultyMember->delete();
    }

    /**
     * Restore a soft-deleted faculty member.
     *
     * @param int $id
     * @return bool
     */
    public function restoreFacultyMember(int $id): bool
    {
        return FacultyMember::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a faculty member permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteFacultyMember(int $id): ?bool
    {
        return FacultyMember::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search faculty members by name or email.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<FacultyMember>|Collection<int, FacultyMember>
     */
    public function searchFacultyMembers(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = FacultyMember::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get active faculty members.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<FacultyMember>|Collection<int, FacultyMember>
     */
    public function getActiveFacultyMembers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = FacultyMember::active()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get faculty members by department.
     *
     * @param int $departmentId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<FacultyMember>|Collection<int, FacultyMember>
     */
    public function getFacultyMembersByDepartment(int $departmentId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = FacultyMember::where('department_id', $departmentId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get faculty members with departments.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $departmentRelations Relations for departments if eager loading is needed for them as well
     * @return Paginator<FacultyMember>|Collection<int, FacultyMember>
     */
    public function getFacultyMembersWithDepartments(int $perPage = 10, array $columns = ['*'], array $departmentRelations = []): Paginator|Collection
    {
        $query = FacultyMember::with(['department' => function ($query) use ($departmentRelations) {
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
     * Get faculty members with subjects.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations Relations for subjects if eager loading is needed for them as well
     * @return Paginator<FacultyMember>|Collection<int, FacultyMember>
     */
    public function getFacultyMembersWithSubjects(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        $query = FacultyMember::with(['subjects' => function ($query) use ($subjectRelations) {
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
     * Add a subject to a faculty member.
     *
     * @param FacultyMember $facultyMember
     * @param int $subjectId
     * @return void
     */
    public function addSubjectToFacultyMember(FacultyMember $facultyMember, int $subjectId): void
    {
        $facultyMember->subjects()->attach($subjectId);
    }

    /**
     * Remove a subject from a faculty member.
     *
     * @param FacultyMember $facultyMember
     * @param int $subjectId
     * @return void
     */
    public function removeSubjectFromFacultyMember(FacultyMember $facultyMember, int $subjectId): void
    {
        $facultyMember->subjects()->detach($subjectId);
    }
}