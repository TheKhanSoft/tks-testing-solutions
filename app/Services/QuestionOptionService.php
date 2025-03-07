<?php

namespace App\Services;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionOptionService
{
    /**
     * Get all question options.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, QuestionOption>
     */
    public function getAllQuestionOptions(array $columns = ['*'], array $relations = []): Collection
    {
        return QuestionOption::with($relations)->get($columns);
    }

    /**
     * Get paginated question options.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<QuestionOption>
     */
    public function getPaginatedQuestionOptions(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return QuestionOption::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a question option by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return QuestionOption|null
     */
    public function getQuestionOptionById(int $id, array $columns = ['*'], array $relations = []): ?QuestionOption
    {
        return QuestionOption::with($relations)->find($id, $columns);
    }

    /**
     * Get a question option by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return QuestionOption
     */
    public function getQuestionOptionByIdOrFail(int $id, array $columns = ['*'], array $relations = []): QuestionOption
    {
        return QuestionOption::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new question option.
     *
     * @param array $data
     * @return QuestionOption
     */
    public function createQuestionOption(array $data): QuestionOption
    {
        return QuestionOption::create($data);
    }

    /**
     * Update an existing question option.
     *
     * @param QuestionOption $questionOption
     * @param array $data
     * @return QuestionOption
     */
    public function updateQuestionOption(QuestionOption $questionOption, array $data): QuestionOption
    {
        $questionOption->update($data);
        return $questionOption;
    }

    /**
     * Delete a question option.
     *
     * @param QuestionOption $questionOption
     * @return bool|null
     */
    public function deleteQuestionOption(QuestionOption $questionOption): ?bool
    {
        return $questionOption->delete();
    }

    /**
     * Restore a soft-deleted question option.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestionOption(int $id): bool
    {
        return QuestionOption::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a question option permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestionOption(int $id): ?bool
    {
        return QuestionOption::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Get correct question options.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<QuestionOption>|Collection<int, QuestionOption>
     */
    public function getCorrectQuestionOptions(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = QuestionOption::correct()->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get question options for a specific question.
     *
     * @param int $questionId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<QuestionOption>|Collection<int, QuestionOption>
     */
    public function getQuestionOptionsByQuestion(int $questionId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = QuestionOption::where('question_id', $questionId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get question options with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations Relations for questions if eager loading is needed for them as well
     * @return Paginator<QuestionOption>|Collection<int, QuestionOption>
     */
    public function getQuestionOptionsWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        $query = QuestionOption::with(['question' => function ($query) use ($questionRelations) {
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