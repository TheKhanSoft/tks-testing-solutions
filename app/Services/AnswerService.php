<?php

namespace App\Services;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class AnswerService extends BaseService
{
    /**
     * AnswerService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Answer::class;
    }
    
    /**
     * Get all answers.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Answer>
     */
    public function getAllAnswers(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated answers.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Answer>
     */
    public function getPaginatedAnswers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get an answer by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Answer|null
     */
    public function getAnswerById(int $id, array $columns = ['*'], array $relations = []): ?Answer
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get an answer by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Answer
     */
    public function getAnswerByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Answer
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new answer.
     *
     * @param array $data
     * @return Answer
     */
    public function createAnswer(array $data): Answer
    {
        return $this->create($data);
    }

    /**
     * Update an existing answer.
     *
     * @param Answer $answer
     * @param array $data
     * @return Answer
     */
    public function updateAnswer(Answer $answer, array $data): Answer
    {
        return $this->update($answer, $data);
    }

    /**
     * Delete an answer.
     *
     * @param Answer $answer
     * @return bool|null
     */
    public function deleteAnswer(Answer $answer): ?bool
    {
        return $this->delete($answer);
    }

    /**
     * Restore a soft-deleted answer.
     *
     * @param int $id
     * @return bool
     */
    public function restoreAnswer(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete an answer permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteAnswer(int $id): ?bool
    {
        return $this->forceDelete($id);
    }

    /**
     * Get answers by test attempt.
     *
     * @param int $testAttemptId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersByTestAttempt(int $testAttemptId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Answer::where('test_attempt_id', $testAttemptId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get answers by question.
     *
     * @param int $questionId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersByQuestion(int $questionId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Answer::where('question_id', $questionId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get correct answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getCorrectAnswers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Answer::where('is_correct', true)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get answers with test attempt.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersWithTestAttempt(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('testAttempt', $testAttemptRelations, $perPage, $columns);
    }

    /**
     * Get answers with question.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersWithQuestion(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('question', $questionRelations, $perPage, $columns);
    }

    /**
     * Get answers with question options.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionOptionRelations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersWithQuestionOptions(int $perPage = 10, array $columns = ['*'], array $questionOptionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('questionOptions', $questionOptionRelations, $perPage, $columns);
    }
}
