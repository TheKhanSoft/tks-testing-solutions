<?php

namespace App\Services;

use App\Models\PaperCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class PaperCategoryService extends BaseService
{
    /**
     * PaperCategoryService constructor.
     */
    public function __construct()
    {
        $this->modelClass = PaperCategory::class;
    }
    
    /**
     * Get all paper categories.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection<int, PaperCategory>
     */
    public function getAllPaperCategories(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->getAll($columns, $relations);
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
        return $this->getPaginated($perPage, $columns, $relations);
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
        return $this->getById($id, $columns, $relations);
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
        return $this->getByIdOrFail($id, $columns, $relations);
    }

    /**
     * Create a new paper category.
     *
     * @param array $data
     * @return PaperCategory
     */
    public function createPaperCategory(array $data): PaperCategory
    {
        return $this->create($data);
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
        return $this->update($paperCategory, $data);
    }

    /**
     * Delete a paper category.
     *
     * @param PaperCategory $paperCategory
     * @return bool|null
     */
    public function deletePaperCategory(PaperCategory $paperCategory): ?bool
    {
        return $this->delete($paperCategory);
    }

    /**
     * Restore a soft-deleted paper category.
     *
     * @param int $id
     * @return bool
     */
    public function restorePaperCategory(int $id): bool
    {
        return $this->restore($id);
    }

    /**
     * Force delete a paper category permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDeletePaperCategory(int $id): ?bool
    {
        return $this->forceDelete($id);
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
        return $this->search($searchTerm, $perPage, $columns, $relations);
    }

    /**
     * Get paper categories with papers.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $paperRelations
     * @return Paginator<PaperCategory>|Collection<int, PaperCategory>
     */
    public function getPaperCategoriesWithPapers(int $perPage = 10, array $columns = ['*'], array $paperRelations = []): Paginator|Collection
    {
        return $this->getWithNestedRelations('papers', $paperRelations, $perPage, $columns);
    }

    /**
     * Get a paper category by name.
     *
     * @param string $name
     * @param array $columns
     * @param array $relations
     * @return PaperCategory|null
     */
    public function getPaperCategoryByName(string $name, array $columns = ['*'], array $relations = []): ?PaperCategory
    {
        return PaperCategory::where('name', $name)->with($relations)->first($columns);
    }

    /**
     * Get popular paper categories.
     *
     * @param int $limit
     * @param array $columns
     * @param array $relations
     * @return Collection<int, PaperCategory>
     */
    public function getPopularPaperCategories(int $limit = 10, array $columns = ['*'], array $relations = []): Collection
    {
        return PaperCategory::withCount('papers')
            ->with($relations)
            ->orderByDesc('papers_count')
            ->limit($limit)
            ->get($columns);
    }
}