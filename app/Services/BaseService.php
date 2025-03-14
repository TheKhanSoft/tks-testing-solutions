<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseService
{
    /**
     * The model class this service manages.
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * Get all records of the managed model.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->modelClass::with($relations)->get($columns);
    }

    /**
     * Get paginated records of the managed model.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator
     */
    public function getPaginated(int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator
    {
        return $this->modelClass::with($relations)->paginate($perPage, $columns);
    }

    /**
     * Get a record by ID.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function getById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->modelClass::with($relations)->find($id, $columns);
    }

    /**
     * Get a record by ID or fail.
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model
     */
    public function getByIdOrFail(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->modelClass::with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update an existing record.
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model;
    }

    /**
     * Delete a record.
     *
     * @param Model $model
     * @return bool|null
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Restore a soft-deleted record.
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        // Only call withTrashed if the model uses SoftDeletes
        if (in_array(SoftDeletes::class, class_uses($this->modelClass))) {
            return $this->modelClass::withTrashed()->findOrFail($id)->restore();
        }
        
        return false;
    }

    /**
     * Force delete a record permanently.
     *
     * @param int $id
     * @return bool|null
     */
    public function forceDelete(int $id): ?bool
    {
        // Only call withTrashed if the model uses SoftDeletes
        if (in_array(SoftDeletes::class, class_uses($this->modelClass))) {
            return $this->modelClass::withTrashed()->findOrFail($id)->forceDelete();
        }
        
        return $this->modelClass::findOrFail($id)->delete();
    }
    
    /**
     * Search records by a given term in searchable fields.
     *
     * @param string $searchTerm
     * @param int|null $perPage
     * @param array $columns
     * @param array $relations
     * @return Paginator|Collection
     */
    public function search(string $searchTerm, ?int $perPage = 10, array $columns = ['*'], array $relations = []): Paginator|Collection
    {
        $query = $this->modelClass::search($searchTerm)->with($relations);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
    
    /**
     * Get records with nested relations optimized to prevent N+1 problems.
     *
     * @param string $relation
     * @param array $nestedRelations
     * @param int|null $perPage
     * @param array $columns
     * @return Paginator|Collection
     */
    public function getWithNestedRelations(string $relation, array $nestedRelations = [], ?int $perPage = 10, array $columns = ['*']): Paginator|Collection
    {
        $query = $this->modelClass::with([
            $relation => function ($query) use ($nestedRelations) {
                if (!empty($nestedRelations)) {
                    $query->with($nestedRelations);
                }
            }
        ]);
        
        if ($perPage) {
            return $query->paginate($perPage, $columns);
        }
        
        return $query->get($columns);
    }
}
