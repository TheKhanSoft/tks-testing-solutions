<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionService
{
    /**
     * Get all questions.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Question>
     */
    public function getAllQuestions(array $columns = ['*'], array $relations = []): Collection
    {
        return Question::with($relations)->get($columns);
    }

    /**
     * Get paginated questions.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>
     */
    public function getPaginatedQuestions(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return Question::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a question by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Question|null
     */
    public function getQuestionById(int $id, array $columns = ['*'], array $relations = []): ?Question
    {
        return Question::with($relations)->find($id, $columns);
    }

    /**
     * Get a question by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Question
     */
    public function getQuestionByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Question
    {
        return Question::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new question.
     *
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data): Question
    {
        return Question::create($data);
    }

    /**
     * Update an existing question.
     *
     * @param Question $question
     * @param array $data
     * @return Question
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        $question->update($data);
        return $question;
    }

    /**
     * Delete a question.
     *
     * @param Question $question
     * @return bool|null
     */
    public function deleteQuestion(Question $question): ?bool
    {
        return $question->delete();
    }

    /**
     * Restore a soft-deleted question.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestion(int $id): bool
    {
        return Question::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a question permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestion(int $id): ?bool
    {
        return Question::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search questions by question text or explanation.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function searchQuestions(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get questions by question type.
     *
     * @param int $questionTypeId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsByType(int $questionTypeId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::ofType($questionTypeId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get questions by subject.
     *
     * @param int $subjectId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsBySubject(int $subjectId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::ofSubject($subjectId)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get questions with question type.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionTypeRelations Relations for question types if eager loading is needed for them as well
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithQuestionType(int $perPage = 10, array $columns = ['*'], array $questionTypeRelations = []): Paginator|Collection
    {
        $query = Question::with(['questionType' => function ($query) use ($questionTypeRelations) {
            if (!empty($questionTypeRelations)) {
                $query->with($questionTypeRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get questions with subject.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations Relations for subjects if eager loading is needed for them as well
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithSubject(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        $query = Question::with(['subject' => function ($query) use ($subjectRelations) {
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
     * Get questions with options.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $optionRelations Relations for options if eager loading is needed for them as well
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithOptions(int $perPage = 10, array $columns = ['*'], array $optionRelations = []): Paginator|Collection
    {
        $query = Question::with(['options' => function ($query) use ($optionRelations) {
            if (!empty($optionRelations)) {
                $query->with($optionRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get questions with answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $answerRelations Relations for answers if eager loading is needed for them as well
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithAnswers(int $perPage = 10, array $columns = ['*'], array $answerRelations = []): Paginator|Collection
    {
        $query = Question::with(['answers' => function ($query) use ($answerRelations) {
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