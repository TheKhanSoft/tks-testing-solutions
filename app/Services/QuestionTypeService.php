<?php

namespace App\Services;

use App\Models\QuestionType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionTypeService
{
    /**
     * Get all question types.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, QuestionType>
     */
    public function getAllQuestionTypes(array $columns = ['*'], array $relations = []): Collection
    {
        return QuestionType::with($relations)->get($columns);
    }

    /**
     * Get paginated question types.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<QuestionType>
     */
    public function getPaginatedQuestionTypes(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return QuestionType::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a question type by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return QuestionType|null
     */
    public function getQuestionTypeById(int $id, array $columns = ['*'], array $relations = []): ?QuestionType
    {
        return QuestionType::with($relations)->find($id, $columns);
    }

    /**
     * Get a question type by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return QuestionType
     */
    public function getQuestionTypeByIdOrFail(int $id, array $columns = ['*'], array $relations = []): QuestionType
    {
        return QuestionType::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new question type.
     *
     * @param array $data
     * @return QuestionType
     */
    public function createQuestionType(array $data): QuestionType
    {
        return QuestionType::create($data);
    }

    /**
     * Update an existing question type.
     *
     * @param QuestionType $questionType
     * @param array $data
     * @return QuestionType
     */
    public function updateQuestionType(QuestionType $questionType, array $data): QuestionType
    {
        $questionType->update($data);
        return $questionType;
    }

    /**
     * Delete a question type.
     *
     * @param QuestionType $questionType
     * @return bool|null
     */
    public function deleteQuestionType(QuestionType $questionType): ?bool
    {
        return $questionType->delete();
    }

    /**
     * Restore a soft-deleted question type.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestionType(int $id): bool
    {
        return QuestionType::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a question type permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestionType(int $id): ?bool
    {
        return QuestionType::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search question types by name or description.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<QuestionType>|Collection<int, QuestionType>
     */
    public function searchQuestionTypes(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = QuestionType::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get question types with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations Relations for questions if eager loading is needed for them as well
     * @return Paginator<QuestionType>|Collection<int, QuestionType>
     */
    public function getQuestionTypesWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        $query = QuestionType::with(['questions' => function ($query) use ($questionRelations) {
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