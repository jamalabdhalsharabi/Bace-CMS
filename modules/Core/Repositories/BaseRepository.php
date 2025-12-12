<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get new query builder.
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Find by ID.
     */
    public function find(string|int $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    /**
     * Find by ID or fail.
     */
    public function findOrFail(string|int $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * Find by column.
     */
    public function findBy(string $column, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->query()->where($column, $value)->first($columns);
    }

    /**
     * Find many by column.
     */
    public function findManyBy(string $column, mixed $value, array $columns = ['*']): Collection
    {
        return $this->query()->where($column, $value)->get($columns);
    }

    /**
     * Find by multiple conditions.
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        $query = $this->query();

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->get($columns);
    }

    /**
     * Create new record.
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * Update record.
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Update by ID.
     */
    public function updateById(string|int $id, array $data): ?Model
    {
        $model = $this->find($id);

        if (!$model) {
            return null;
        }

        return $this->update($model, $data);
    }

    /**
     * Delete record.
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Delete by ID.
     */
    public function deleteById(string|int $id): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $this->delete($model);
    }

    /**
     * Force delete record.
     */
    public function forceDelete(Model $model): bool
    {
        return $model->forceDelete();
    }

    /**
     * Restore soft deleted record.
     */
    public function restore(Model $model): bool
    {
        return $model->restore();
    }

    /**
     * Check if record exists.
     */
    public function exists(string|int $id): bool
    {
        return $this->query()->where('id', $id)->exists();
    }

    /**
     * Count records.
     */
    public function count(): int
    {
        return $this->query()->count();
    }

    /**
     * Get with relations.
     */
    public function with(array|string $relations): static
    {
        $this->model = $this->query()->with($relations)->getModel();
        return $this;
    }

    /**
     * Order by.
     */
    public function orderBy(string $column, string $direction = 'asc'): Builder
    {
        return $this->query()->orderBy($column, $direction);
    }

    /**
     * Get first or create.
     */
    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->query()->firstOrCreate($attributes, $values);
    }

    /**
     * Update or create.
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->query()->updateOrCreate($attributes, $values);
    }

    /**
     * Get only trashed.
     */
    public function onlyTrashed(): Builder
    {
        return $this->query()->onlyTrashed();
    }

    /**
     * Get with trashed.
     */
    public function withTrashed(): Builder
    {
        return $this->query()->withTrashed();
    }
}
