<?php

namespace App\Services;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class AnswerService
{
    /**
     * Get all answers.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Answer>
     */
    public function getAllAnswers(array $columns = ['*'], array $relations = []): Collection
    {
        return Answer::with($relations)->get($columns);
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
        return Answer::with($relations)->paginate($perPage, $columns);
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
        return Answer::with($relations)->find($id, $columns);
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
        return Answer::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new answer.
     *
     * @param array $data
     * @return Answer
     */
    public function createAnswer(array $data): Answer
    {
        return Answer::create($data);
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
        $answer->update($data);
        return $answer;
    }

    /**
     * Delete an answer.
     *
     * @param Answer $answer
     * @return bool|null
     */
    public function deleteAnswer(Answer $answer): ?bool
    {
        return $answer->delete();
    }

    /**
     * Restore a soft-deleted answer.
     *
     * @param int $id
     * @return bool
     */
    public function restoreAnswer(int $id): bool
    {
        return Answer::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete an answer permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteAnswer(int $id): ?bool
    {
        return Answer::withTrashed()->findOrFail($id)->forceDelete();
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
        $query = Answer::correct()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get incorrect answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getIncorrectAnswers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Answer::incorrect()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
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
        $query = Answer::forTestAttempt($testAttemptId)->with($relations);
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
        $query = Answer::forQuestion($questionId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get answers with test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations Relations for test attempts if eager loading is needed for them as well
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersWithTestAttempts(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        $query = Answer::with(['testAttempt' => function ($query) use ($testAttemptRelations) {
            if (!empty($testAttemptRelations)) {
                $query->with($testAttemptRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get answers with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations Relations for questions if eager loading is needed for them as well
     * @return Paginator<Answer>|Collection<int, Answer>
     */
    public function getAnswersWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        $query = Answer::with(['question' => function ($query) use ($questionRelations) {
            if (!empty($questionRelations)) {
                $query->with($questionRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }
}