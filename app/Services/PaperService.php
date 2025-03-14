<?php

namespace App\Services;

use App\Models\Paper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class PaperService extends BaseService
{
    /**
     * PaperService constructor.
     */
    public function __construct()
    {
        $this->modelClass = Paper::class;
    }
    
    /**
     * Get all papers.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Paper>
     */
    public function getAllPapers(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
    }

    /**
     * Get paginated papers.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>
     */
    public function getPaginatedPapers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->getPaginated($perPage, $columns, $relations);
    }

    /**
     * Get a paper by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Paper|null
     */
    public function getPaperById(int $id, array $columns = ['*'], array $relations = []): ?Paper
    {
        return $this->getById($id, $columns, $relations);
    }

    /**
     * Get a paper by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Paper
     */
    public function getPaperByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Paper
    {
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new paper.
     *
     * @param array $data
     * @return Paper
     */
    public function createPaper(array $data): Paper
    {
        return $this->create($data);
    }

    /**
     * Update an existing paper.
     *
     * @param Paper $paper
     * @param array $data
     * @return Paper
     */
    public function updatePaper(Paper $paper, array $data): Paper
    {
        return $this->update($paper, $data);
    }

    /**
     * Delete a paper.
     *
     * @param Paper $paper
     * @return bool|null
     */
    public function deletePaper(Paper $paper): ?bool
    {
        return $this->delete($paper);
    }

    /**
     * Restore a soft-deleted paper.
     *
     * @param int $id
     * @return bool
     */
    public function restorePaper(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a paper permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeletePaper(int $id): ?bool
    {
        return $this->forceDelete($id);
    }

    /**
     * Search papers by name, description, subject, or category.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function searchPapers(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get published papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPublishedPapers(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Paper::published()->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get papers by category.
     *
     * @param int $paperCategoryId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersByCategory(int $paperCategoryId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Paper::ofCategory($paperCategoryId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get papers by subject.
     *
     * @param int $subjectId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersBySubject(int $subjectId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Paper::ofSubject($subjectId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get papers with subject.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $subjectRelations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithSubject(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('subject', $subjectRelations, $perPage, $columns);
    }

    /**
     * Get papers with paper category.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperCategoryRelations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithPaperCategory(int $perPage = 10, array $columns = ['*'], array $paperCategoryRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('paperCategory', $paperCategoryRelations, $perPage, $columns);
    }

    /**
     * Get papers with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('questions', $questionRelations, $perPage, $columns);
    }

    /**
     * Get papers with user categories.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userCategoryRelations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithUserCategories(int $perPage = 10, array $columns = ['*'], array $userCategoryRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('userCategories', $userCategoryRelations, $perPage, $columns);
    }

    /**
     * Get papers with test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithTestAttempts(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('testAttempts', $testAttemptRelations, $perPage, $columns);
    }

    /**
     * Add a question to a paper.
     *
     * @param Paper $paper
     * @param int $questionId
     * @param int|null $orderIndex
     * @return void
     */
    public function addQuestionToPaper(Paper $paper, int $questionId, ?int $orderIndex = null): void
    {
        $paper->questions()->attach($questionId, ['order_index' => $orderIndex]);
    }

    /**
     * Remove a question from a paper.
     *
     * @param Paper $paper
     * @param int $questionId
     * @return void
     */
    public function removeQuestionFromPaper(Paper $paper, int $questionId): void
    {
        $paper->questions()->detach($questionId);
    }

    /**
     * Add a user category to a paper.
     *
     * @param Paper $paper
     * @param int $userCategoryId
     * @return void
     */
    public function addUserCategoryToPaper(Paper $paper, int $userCategoryId): void
    {
        $paper->userCategories()->attach($userCategoryId);
    }

    /**
     * Remove a user category from a paper.
     *
     * @param Paper $paper
     * @param int $userCategoryId
     * @return void
     */
    public function removeUserCategoryFromPaper(Paper $paper, int $userCategoryId): void
    {
        $paper->userCategories()->detach($userCategoryId);
    }

    /**
     * Get papers by difficulty level.
     *
     * @param int $difficultyLevel
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersByDifficultyLevel(int $difficultyLevel, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Paper::where('difficulty_level', $difficultyLevel)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }

    /**
     * Get papers available for a specific user.
     *
     * @param int $userId
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getAvailablePapersForUser(int $userId, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = Paper::availableForUser($userId)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
}