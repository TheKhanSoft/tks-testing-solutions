<?php

namespace App\Services;

use App\Models\Paper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class PaperService
{
    /**
     * Get all papers.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, Paper>
     */
    public function getAllPapers(array $columns = ['*'], array $relations = []): Collection
    {
        return Paper::with($relations)->get($columns);
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
        return Paper::with($relations)->paginate($perPage, $columns);
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
        return Paper::with($relations)->find($id, $columns);
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
        return Paper::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new paper.
     *
     * @param array $data
     * @return Paper
     */
    public function createPaper(array $data): Paper
    {
        return Paper::create($data);
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
        $paper->update($data);
        return $paper;
    }

    /**
     * Delete a paper.
     *
     * @param Paper $paper
     * @return bool|null
     */
    public function deletePaper(Paper $paper): ?bool
    {
        return $paper->delete();
    }

    /**
     * Restore a soft-deleted paper.
     *
     * @param int $id
     * @return bool
     */
    public function restorePaper(int $id): bool
    {
        return Paper::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a paper permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeletePaper(int $id): ?bool
    {
        return Paper::withTrashed()->findOrFail($id)->forceDelete();
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
        $query = Paper::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
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
     * @param array $subjectRelations Relations for subjects if eager loading is needed for them as well
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithSubject(int $perPage = 10, array $columns = ['*'], array $subjectRelations = []): Paginator|Collection
    {
        $query = Paper::with(['subject' => function ($query) use ($subjectRelations) {
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
     * Get papers with paper category.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperCategoryRelations Relations for paper categories if eager loading is needed for them as well
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithPaperCategory(int $perPage = 10, array $columns = ['*'], array $paperCategoryRelations = []): Paginator|Collection
    {
        $query = Paper::with(['paperCategory' => function ($query) use ($paperCategoryRelations) {
            if (!empty($paperCategoryRelations)) {
                $query->with($paperCategoryRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get papers with questions.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $questionRelations Relations for questions if eager loading is needed for them as well
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithQuestions(int $perPage = 10, array $columns = ['*'], array $questionRelations = []): Paginator|Collection
    {
        $query = Paper::with(['questions' => function ($query) use ($questionRelations) {
            if (!empty($questionRelations)) {
                $query->with($questionRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get papers with user categories.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $userCategoryRelations Relations for user categories if eager loading is needed for them as well
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithUserCategories(int $perPage = 10, array $columns = ['*'], array $userCategoryRelations = []): Paginator|Collection
    {
        $query = Paper::with(['userCategories' => function ($query) use ($userCategoryRelations) {
            if (!empty($userCategoryRelations)) {
                $query->with($userCategoryRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get papers with test attempts.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $testAttemptRelations Relations for test attempts if eager loading is needed for them as well
     * @return Paginator<Paper>|Collection<int, Paper>
     */
    public function getPapersWithTestAttempts(int $perPage = 10, array $columns = ['*'], array $testAttemptRelations = []): Paginator|Collection
    {
        $query = Paper::with(['testAttempts' => function ($query) use ($testAttemptRelations) {
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
}