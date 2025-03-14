<?php

namespace App\Services;

use App\Models\TestAttempt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class TestAttemptService extends BaseService
{
    /**
     * TestAttemptService constructor.
     */
    public function __construct()
    {
        $this->modelClass = TestAttempt::class;
    }
    
    /**
     * Get all test attempts.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, TestAttempt>
     */
    public function getAllTestAttempts(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new test attempt.
     *
     * @param array $data
     * @return TestAttempt
     */
    public function createTestAttempt(array $data): TestAttempt
    {
        return $this->create($data);
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
        return $this->update($testAttempt, $data);
    }

    /**
     * Delete a test attempt.
     *
     * @param TestAttempt $testAttempt
     * @return bool|null
     */
    public function deleteTestAttempt(TestAttempt $testAttempt): ?bool
    {
        return $this->delete($testAttempt);
    }

    /**
     * Restore a soft-deleted test attempt.
     *
     * @param int $id
     * @return bool
     */
    public function restoreTestAttempt(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a test attempt permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteTestAttempt(int $id): ?bool
    {
        return $this->forceDelete($id);
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
     * @param array $userRelations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithUsers(int $perPage = 10, array $columns = ['*'], array $userRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('user', $userRelations, $perPage, $columns);
    }

    /**
     * Get test attempts with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('paper', $paperRelations, $perPage, $columns);
    }

    /**
     * Get test attempts with answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $answerRelations
     * @return Paginator<TestAttempt>|Collection<int, TestAttempt>
     */
    public function getTestAttemptsWithAnswers(int $perPage = 10, array $columns = ['*'], array $answerRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('answers', $answerRelations, $perPage, $columns);
    }
    
    /**
     * Get score for a test attempt.
     *
     * @param TestAttempt $testAttempt
     * @return float|int
     */
    public function getTestAttemptScore(TestAttempt $testAttempt): float|int
    {
        return $testAttempt->calculateScore();
    }
    
    /**
     * Mark a test attempt as completed.
     *
     * @param TestAttempt $testAttempt
     * @return TestAttempt
     */
    public function completeTestAttempt(TestAttempt $testAttempt): TestAttempt
    {
        $testAttempt->completed_at = now();
        $testAttempt->save();
        
        return $testAttempt;
    }
    
    /**
     * Get recent test attempts.
     *
     * @param int $limit
     * @param array $columns
     * @param array $relations
     * @return Collection<int, TestAttempt>
     */
    public function getRecentTestAttempts(int $limit = 10, array $columns = ['*'], array $relations = []): Collection
    {
        return TestAttempt::with($relations)->latest('created_at')->limit($limit)->get($columns);
    }
}