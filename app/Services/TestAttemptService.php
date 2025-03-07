<?php

namespace App\Services;

use App\Models\TestAttempt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class TestAttemptService
{
    /**
     * Get all test attempts.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, TestAttempt>
     */
    public function getAllTestAttempts(array $columns = ['*'], array $relations = []): Collection
    {
        return TestAttempt::with($relations)->get($columns);
    }

    /**
     * Get paginated test attempts.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<TestAttempt>
     */
    public function getPaginatedTestAttempts(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return TestAttempt::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a test attempt by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return TestAttempt|null
     */
    public function getTestAttemptById(int $id, array $columns = ['*'], array $relations = []): ?TestAttempt
    {
        return TestAttempt::with($relations)->find($id, $columns);
    }

    /**
     * Get a test attempt by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return TestAttempt
     */
    public function getTestAttemptByIdOrFail(int $id, array $columns = ['*'], array $relations = []): TestAttempt
    {
        return TestAttempt::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new test attempt.
     *
     * @param array $data
     * @return TestAttempt
     */
    public function createTestAttempt(array $data): TestAttempt
    {
        return TestAttempt::create($data);
    }

    /**
     * Update an existing test attempt.
     *
     * @param TestAttempt $testAttempt
     * @param array $data
     * @return TestAttempt
     */
    public function updateTestAttempt(TestAttempt $testAttempt, array $data): TestAttempt
    {
        $testAttempt->update($data);
        return $testAttempt;
    }

    /**
     * Delete a test attempt.
     *
     * @param TestAttempt $testAttempt
     * @return bool|null
     */
    public function deleteTestAttempt(TestAttempt $testAttempt): ?bool
    {
        return $testAttempt->delete();
    }

    /**
     * Restore a soft-deleted test attempt.
     *
     * @param int $id
     * @return bool
     */
    public function restoreTestAttempt(int $id): bool
    {
        return TestAttempt::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a test attempt permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteTestAttempt(int $id): ?bool
    {
        return TestAttempt::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Get in progress test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getInProgressTestAttempts(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = TestAttempt::inProgress()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get completed test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getCompletedTestAttempts(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = TestAttempt::completed()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get test attempts by user.
     *
     * @param int $userId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsByUser(int $userId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = TestAttempt::forUser($userId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get test attempts by paper.
     *
     * @param int $paperId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsByPaper(int $paperId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = TestAttempt::forPaper($paperId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get test attempts with users.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userRelations Relations for users if eager loading is needed for them as well
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithUsers(int $perPage = 10, array $columns = ['*'], array $userRelations = []): Paginator|Collection
    {
        $query = TestAttempt::with(['user' => function ($query) use ($userRelations) {
            if (!empty($userRelations)) {
                $query->with($userRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get test attempts with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations Relations for papers if eager loading is needed for them as well
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        $query = TestAttempt::with(['paper' => function ($query) use ($paperRelations) {
            if (!empty($paperRelations)) {
                $query->with($paperRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get test attempts with answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $answerRelations Relations for answers if eager loading is needed for them as well
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithAnswers(int $perPage = 10, array $columns = ['*'], array $answerRelations = []): Paginator|Collection
    {
        $query = TestAttempt::with(['answers' => function ($query) use ($answerRelations) {
            if (!empty($answerRelations)) {
                $query->with($answerRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }
}