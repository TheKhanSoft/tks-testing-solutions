<?php

namespace App\Services;

use App\Models\PaperCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class PaperCategoryService
{
    /**
     * Get all paper categories.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, PaperCategory>
     */
    public function getAllPaperCategories(array $columns = ['*'], array $relations = []): Collection
    {
        return PaperCategory::with($relations)->get($columns);
    }

    /**
     * Get paginated paper categories.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<PaperCategory>
     */
    public function getPaginatedPaperCategories(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return PaperCategory::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a paper category by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return PaperCategory|null
     */
    public function getPaperCategoryById(int $id, array $columns = ['*'], array $relations = []): ?PaperCategory
    {
        return PaperCategory::with($relations)->find($id, $columns);
    }

    /**
     * Get a paper category by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return PaperCategory
     */
    public function getPaperCategoryByIdOrFail(int $id, array $columns = ['*'], array $relations = []): PaperCategory
    {
        return PaperCategory::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new paper category.
     *
     * @param array $data
     * @return PaperCategory
     */
    public function createPaperCategory(array $data): PaperCategory
    {
        return PaperCategory::create($data);
    }

    /**
     * Update an existing paper category.
     *
     * @param PaperCategory $paperCategory
     * @param array $data
     * @return PaperCategory
     */
    public function updatePaperCategory(PaperCategory $paperCategory, array $data): PaperCategory
    {
        $paperCategory->update($data);
        return $paperCategory;
    }

    /**
     * Delete a paper category.
     *
     * @param PaperCategory $paperCategory
     * @return bool|null
     */
    public function deletePaperCategory(PaperCategory $paperCategory): ?bool
    {
        return $paperCategory->delete();
    }

    /**
     * Restore a soft-deleted paper category.
     *
     * @param int $id
     * @return bool
     */
    public function restorePaperCategory(int $id): bool
    {
        return PaperCategory::withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Force delete a paper category permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeletePaperCategory(int $id): ?bool
    {
        return PaperCategory::withTrashed()->findOrFail($id)->forceDelete();
    }

    /**
     * Search paper categories by name or description.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator<PaperCategory>|Collection<int, PaperCategory>
     */
    public function searchPaperCategories(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = PaperCategory::search($searchTerm)->with($relations);
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }

    /**
     * Get paper categories with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations Relations for papers if eager loading is needed for them as well
     * @return Paginator<PaperCategory>|Collection<int, PaperCategory>
     */
    public function getPaperCategoriesWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        $query = PaperCategory::with(['papers' => function ($query) use ($paperRelations) {
            if (!empty($paperRelations)) {
                $query->with($paperRelations);
            }
        }]);

        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        return $query->get($columns);
    }
}