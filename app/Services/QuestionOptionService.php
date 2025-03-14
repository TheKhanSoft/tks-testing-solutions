<?php

namespace App\Services;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionOptionService extends BaseService
{
    /**
     * QuestionOptionService constructor.
     */
    public function __construct()
    {
        $this->modelClass = QuestionOption::class;
    }
    
    /**
     * Get all question options.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, QuestionOption>
     */
    public function getAllQuestionOptions(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new question option.
     *
     * @param array $data
     * @return QuestionOption
     */
    public function createQuestionOption(array $data): QuestionOption
    {
        return $this->create($data);
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
        return $this->update($questionOption, $data);
    }

    /**
     * Delete a question option.
     *
     * @param QuestionOption $questionOption
     * @return bool|null
     */
    public function deleteQuestionOption(QuestionOption $questionOption): ?bool
    {
        return $this->delete($questionOption);
    }

    /**
     * Restore a soft-deleted question option.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestionOption(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a question option permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestionOption(int $id): ?bool
    {
        return $this->forceDelete($id);
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
     * @param array $questionRelations
     * @return Paginator<QuestionOption>|Collection<int, QuestionOption>
     */
    public function getQuestionOptionsWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('question', $questionRelations, $perPage, $columns);
    }

    /**
     * Create multiple question options at once.
     *
     * @param int $questionId
     * @param array $optionsData
     * @return Collection<int, QuestionOption>
     */
    public function createMultipleOptions(int $questionId, array $optionsData): Collection
    {
        $options = collect();
        
        foreach ($optionsData as $optionData) {
            $optionData['question_id'] = $questionId;
            $options->push($this->createQuestionOption($optionData));
        }
        
        return $options;
    }

    /**
     * Get option selected in an answer.
     *
     * @param int $answerId
     * @param array $columns
     * @param array $relations
     * @return Collection<int, QuestionOption>
     */
    public function getOptionsForAnswer(int $answerId, array $columns = ['*'], array $relations = []): Collection
    {
        return QuestionOption::whereHas('answers', function($query) use ($answerId) {
            $query->where('answer_id', $answerId);
        })->with($relations)->get($columns);
    }
}