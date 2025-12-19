<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Domain\Contracts\RepositoryInterface;

/**
 * Base Repository Implementation.
 *
 * Abstract class providing the default implementation of RepositoryInterface.
 * All module-specific repositories should extend this class to inherit
 * common CRUD operations and query building functionality.
 *
 * This implementation:
 * - Provides standard CRUD operations (Create, Read, Update, Delete)
 * - Supports eager loading of relationships
 * - Works with UUID primary keys
 * - Is compatible with soft deletes
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @implements RepositoryInterface<TModel>
 *
 * @package Modules\Core\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see RepositoryInterface The interface this class implements
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The Eloquent model instance.
     *
     * Holds the model that this repository operates on.
     * Injected via constructor dependency injection.
     *
     * @var TModel
     */
    protected Model $model;

    /**
     * Relationships to eager load on queries.
     *
     * Array of relationship names that will be automatically
     * eager loaded when executing queries through this repository.
     *
     * @var array<int, string>
     */
    protected array $with = [];

    /**
     * Create a new repository instance.
     *
     * @param TModel $model The Eloquent model instance to operate on
     *
     * @example
     * ```php
     * // In a concrete repository
     * public function __construct(Article $model)
     * {
     *     parent::__construct($model);
     * }
     * ```
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     *
     * Retrieves all records from the model's table.
     * Respects any eager loading set via with().
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * {@inheritdoc}
     *
     * Returns a Laravel paginator instance with metadata
     * including total count, current page, and per-page info.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * {@inheritdoc}
     *
     * Performs a primary key lookup using UUID.
     * Returns null for non-existent records.
     */
    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * {@inheritdoc}
     *
     * Throws ModelNotFoundException if record doesn't exist,
     * which Laravel converts to a 404 HTTP response.
     */
    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     *
     * Performs an exact match query on the specified field.
     * Returns all matching records as a collection.
     */
    public function findBy(string $field, mixed $value): Collection
    {
        return $this->query()->where($field, $value)->get();
    }

    /**
     * {@inheritdoc}
     *
     * Similar to findBy but returns only the first match.
     * Useful for unique field lookups (email, slug, etc.).
     */
    public function findFirstBy(string $field, mixed $value): ?Model
    {
        return $this->query()->where($field, $value)->first();
    }

    /**
     * {@inheritdoc}
     *
     * Creates a new record using mass assignment.
     * Respects the model's $fillable and $guarded properties.
     */
    public function create(array $data): Model
    {
        return $this->model->newInstance()->create($data);
    }

    /**
     * {@inheritdoc}
     *
     * Updates the record and returns a fresh instance
     * to ensure all computed attributes are current.
     */
    public function update(string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * {@inheritdoc}
     *
     * Performs soft delete if model uses SoftDeletes trait,
     * otherwise performs permanent deletion.
     */
    public function delete(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * {@inheritdoc}
     *
     * Returns a fresh query builder with any configured
     * eager loading relationships applied.
     */
    public function query(): Builder
    {
        $query = $this->model->newQuery();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * Sets relationships to eager load. Overwrites any
     * previously set relationships.
     */
    public function with(array|string $relations): static
    {
        $this->with = is_array($relations) ? $relations : [$relations];

        return $this;
    }

    /**
     * Reset eager loading relationships.
     *
     * Clears any previously set eager loading relationships.
     * Useful when you need to perform a query without any
     * relationships after having set some.
     *
     * @return static Returns self for method chaining
     *
     * @example
     * ```php
     * // Load with relationships first
     * $articles = $repository->with(['author'])->all();
     *
     * // Then without relationships
     * $simpleArticles = $repository->resetWith()->all();
     * ```
     */
    public function resetWith(): static
    {
        $this->with = [];

        return $this;
    }

    /**
     * Get the underlying model instance.
     *
     * Provides access to the model for cases where direct
     * model access is needed (e.g., for table name, etc.).
     *
     * @return TModel The model instance
     *
     * @example
     * ```php
     * $tableName = $repository->getModel()->getTable();
     * ```
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Check if a record exists by ID.
     *
     * Performs an existence check without retrieving the full record.
     * More efficient than find() when you only need to verify existence.
     *
     * @param string $id The record's UUID primary key
     *
     * @return bool True if record exists, false otherwise
     *
     * @example
     * ```php
     * if ($repository->exists($id)) {
     *     // Record exists
     * }
     * ```
     */
    public function exists(string $id): bool
    {
        return $this->query()->where($this->model->getKeyName(), $id)->exists();
    }

    /**
     * Count all records in the repository.
     *
     * Returns the total number of records. Does not account
     * for soft deleted records unless using withTrashed().
     *
     * @return int Total record count
     *
     * @example
     * ```php
     * $totalArticles = $repository->count();
     * ```
     */
    public function count(): int
    {
        return $this->query()->count();
    }
}
