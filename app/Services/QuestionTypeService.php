<?php

namespace App\Services;

use App\Models\QuestionType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionTypeService extends BaseService
{
    /**
     * QuestionTypeService constructor.
     */
    public function __construct()
    {
        $this->modelClass = QuestionType::class;
    }
    
    /**
     * Get all question types.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, QuestionType>
     */
    public function getAllQuestionTypes(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new question type.
     *
     * @param array $data
     * @return QuestionType
     */
    public function createQuestionType(array $data): QuestionType
    {
        return $this->create($data);
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
        return $this->update($questionType, $data);
    }

    /**
     * Delete a question type.
     *
     * @param QuestionType $questionType
     * @return bool|null
     */
    public function deleteQuestionType(QuestionType $questionType): ?bool
    {
        return $this->delete($questionType);
    }

    /**
     * Restore a soft-deleted question type.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestionType(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a question type permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestionType(int $id): ?bool
    {
        return $this->forceDelete($id);
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
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get question types with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations
     * @return Paginator<QuestionType>|Collection<int, QuestionType>
     */
    public function getQuestionTypesWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('questions', $questionRelations, $perPage, $columns);
    }
    
    /**
     * Get question types by usage frequency.
     *
     * @param int $limit
     * @param array $columns
     * @return Collection<int, QuestionType>
     */
    public function getQuestionTypesByUsage(int $limit = 10, array $columns = ['*']): Collection
    {
        return QuestionType::withCount('questions')
            ->orderByDesc('questions_count')
            ->limit($limit)
            ->get($columns);
    }

    /**
     * Get a question type by name.
     *
     * @param string $name
     * @param array $columns
     * @param array $relations
     * @return QuestionType|null
     */
    public function getQuestionTypeByName(string $name, array $columns = ['*'], array $relations = []): ?QuestionType
    {
        return QuestionType::where('name', $name)->with($relations)->first($columns);
    }
}