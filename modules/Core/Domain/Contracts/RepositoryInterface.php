<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Repository Interface.
 *
 * Defines the contract for all repository implementations
 * following the Repository Pattern for data access abstraction.
 *
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * Get all records.
     *
     * @param array<string> $columns Columns to select
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     *
     * @param int $perPage Items per page
     * @param array<string> $columns Columns to select
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a record by ID.
     *
     * @param string $id Record UUID
     * @return TModel|null
     */
    public function find(string $id): ?Model;

    /**
     * Find a record by ID or throw exception.
     *
     * @param string $id Record UUID
     * @return TModel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(string $id): Model;

    /**
     * Find records by a field value.
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return Collection<int, TModel>
     */
    public function findBy(string $field, mixed $value): Collection;

    /**
     * Find first record by a field value.
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return TModel|null
     */
    public function findFirstBy(string $field, mixed $value): ?Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data Record data
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     *
     * @param string $id Record UUID
     * @param array<string, mixed> $data Updated data
     * @return TModel
     */
    public function update(string $id, array $data): Model;

    /**
     * Delete a record.
     *
     * @param string $id Record UUID
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Get a new query builder instance.
     *
     * @return Builder<TModel>
     */
    public function query(): Builder;

    /**
     * Eager load relationships.
     *
     * @param array<string>|string $relations Relations to load
     * @return static
     */
    public function with(array|string $relations): static;
}
