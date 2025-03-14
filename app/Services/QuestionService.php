<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class QuestionService extends BaseService
{
    /**
     * QuestionService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Question::class;
    }
    
    /**
     * Get all questions.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Question>
     */
    public function getAllQuestions(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new question.
     *
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data): Question
    {
        return $this->create($data);
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
        return $this->update($question, $data);
    }

    /**
     * Delete a question.
     *
     * @param Question $question
     * @return bool|null
     */
    public function deleteQuestion(Question $question): ?bool
    {
        return $this->delete($question);
    }

    /**
     * Restore a soft-deleted question.
     *
     * @param int $id
     * @return bool
     */
    public function restoreQuestion(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a question permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeleteQuestion(int $id): ?bool
    {
        return $this->forceDelete($id);
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
        return $this->search($searchTerm, $perPage, $columns, $relations);
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
     * @param array $questionTypeRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithQuestionType(int $perPage = 10, array $columns = ['*'], array $questionTypeRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('questionType', $questionTypeRelations, $perPage, $columns);
    }

    /**
     * Get questions with subject.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithSubject(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('subject', $subjectRelations, $perPage, $columns);
    }

    /**
     * Get questions with options.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $optionRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithOptions(int $perPage = 10, array $columns = ['*'], array $optionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('options', $optionRelations, $perPage, $columns);
    }

    /**
     * Get questions with answers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $answerRelations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsWithAnswers(int $perPage = 10, array $columns = ['*'], array $answerRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('answers', $answerRelations, $perPage, $columns);
    }
    
    /**
     * Get random questions.
     *
     * @param int $count
     * @param array|null $subjectIds
     * @param array|null $questionTypeIds
     * @param array $relations
     * @return Collection<int, Question>
     */
    public function getRandomQuestions(int $count, ?array $subjectIds = null, ?array $questionTypeIds = null, array $relations = []): Collection
    {
        $query = Question::query()->with($relations);
        
        if ($subjectIds) {
            $query->whereIn('subject_id', $subjectIds);
        }
        
        if ($questionTypeIds) {
            $query->whereIn('question_type_id', $questionTypeIds);
        }
        
        return $query->inRandomOrder()->limit($count)->get();
    }
    
    /**
     * Get questions for a paper.
     *
     * @param int $paperId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsForPaper(int $paperId, ?int $perPage = null, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::whereHas('papers', function($query) use ($paperId) {
            $query->where('paper_id', $paperId);
        })->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get questions by difficulty level.
     *
     * @param int $difficultyLevel
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Question>|Collection<int, Question>
     */
    public function getQuestionsByDifficultyLevel(int $difficultyLevel, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Question::where('difficulty_level', $difficultyLevel)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
}