<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Repository Interface Contract.
 *
 * Defines the standard contract for all repository implementations in the application.
 * This interface follows the Repository Pattern to provide a clean abstraction layer
 * between the domain/business logic and data access logic.
 *
 * Benefits of using this interface:
 * - Decouples business logic from data persistence mechanisms
 * - Enables easy unit testing through dependency injection and mocking
 * - Allows swapping data sources without affecting business logic
 * - Provides consistent API across all data access operations
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @package Modules\Core\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see \Modules\Core\Domain\Repositories\BaseRepository Default implementation
 */
interface RepositoryInterface
{
    /**
     * Retrieve all records from the repository.
     *
     * Returns all records from the underlying data store. Use with caution
     * on large datasets - consider using paginate() instead.
     *
     * @param array<int, string> $columns Columns to select (default: all columns)
     *
     * @return Collection<int, TModel> Collection of model instances
     *
     * @example
     * ```php
     * // Get all users
     * $users = $repository->all();
     *
     * // Get only specific columns
     * $users = $repository->all(['id', 'email', 'status']);
     * ```
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Retrieve paginated records from the repository.
     *
     * Returns a paginated result set suitable for displaying in lists
     * with pagination controls.
     *
     * @param int               $perPage Number of items per page (default: 15)
     * @param array<int, string> $columns Columns to select (default: all columns)
     *
     * @return LengthAwarePaginator Paginated result with metadata
     *
     * @example
     * ```php
     * // Get paginated articles (15 per page)
     * $articles = $repository->paginate();
     *
     * // Custom pagination
     * $articles = $repository->paginate(25, ['id', 'title', 'status']);
     * ```
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a single record by its primary key (UUID).
     *
     * Returns null if no record is found, making it safe for conditional checks.
     *
     * @param string $id The record's UUID primary key
     *
     * @return TModel|null The model instance or null if not found
     *
     * @example
     * ```php
     * $article = $repository->find('550e8400-e29b-41d4-a716-446655440000');
     * if ($article) {
     *     // Process article
     * }
     * ```
     */
    public function find(string $id): ?Model;

    /**
     * Find a single record by its primary key or throw an exception.
     *
     * Use this method when the record must exist. Throws ModelNotFoundException
     * if the record is not found, which Laravel automatically converts to a 404 response.
     *
     * @param string $id The record's UUID primary key
     *
     * @return TModel The model instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When record not found
     *
     * @example
     * ```php
     * // Will throw 404 if not found
     * $article = $repository->findOrFail($id);
     * ```
     */
    public function findOrFail(string $id): Model;

    /**
     * Find all records matching a specific field value.
     *
     * Useful for retrieving multiple records that share a common attribute value.
     *
     * @param string $field The database column name to search
     * @param mixed  $value The value to match against
     *
     * @return Collection<int, TModel> Collection of matching model instances
     *
     * @example
     * ```php
     * // Get all articles by a specific author
     * $articles = $repository->findBy('author_id', $authorId);
     *
     * // Get all active users
     * $users = $repository->findBy('status', 'active');
     * ```
     */
    public function findBy(string $field, mixed $value): Collection;

    /**
     * Find the first record matching a specific field value.
     *
     * Returns only the first matching record or null if none found.
     *
     * @param string $field The database column name to search
     * @param mixed  $value The value to match against
     *
     * @return TModel|null The first matching model instance or null
     *
     * @example
     * ```php
     * // Find user by email
     * $user = $repository->findFirstBy('email', 'user@example.com');
     *
     * // Find article by slug
     * $article = $repository->findFirstBy('slug', 'my-article-slug');
     * ```
     */
    public function findFirstBy(string $field, mixed $value): ?Model;

    /**
     * Create a new record in the repository.
     *
     * Persists a new record to the data store with the provided attributes.
     * The model's fillable/guarded rules are respected.
     *
     * @param array<string, mixed> $data Associative array of column => value pairs
     *
     * @return TModel The newly created model instance with ID populated
     *
     * @throws \Illuminate\Database\QueryException On database errors
     *
     * @example
     * ```php
     * $article = $repository->create([
     *     'title' => 'New Article',
     *     'content' => 'Article content...',
     *     'status' => 'draft',
     *     'author_id' => request()->user()?->id,
     * ]);
     * ```
     */
    public function create(array $data): Model;

    /**
     * Update an existing record in the repository.
     *
     * Updates the record identified by the given ID with the provided data.
     * Returns a fresh instance of the model after update.
     *
     * @param string               $id   The record's UUID primary key
     * @param array<string, mixed> $data Associative array of column => value pairs to update
     *
     * @return TModel The updated model instance (fresh from database)
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When record not found
     * @throws \Illuminate\Database\QueryException On database errors
     *
     * @example
     * ```php
     * $article = $repository->update($id, [
     *     'title' => 'Updated Title',
     *     'status' => 'published',
     * ]);
     * ```
     */
    public function update(string $id, array $data): Model;

    /**
     * Delete a record from the repository.
     *
     * Performs a soft delete if the model uses SoftDeletes trait,
     * otherwise performs a permanent delete.
     *
     * @param string $id The record's UUID primary key
     *
     * @return bool True if deletion was successful, false otherwise
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When record not found
     *
     * @example
     * ```php
     * if ($repository->delete($id)) {
     *     // Record deleted successfully
     * }
     * ```
     */
    public function delete(string $id): bool;

    /**
     * Get a new Eloquent query builder instance.
     *
     * Provides access to the underlying query builder for complex queries
     * that aren't covered by the standard repository methods.
     *
     * @return Builder<TModel> Fresh query builder instance
     *
     * @example
     * ```php
     * // Complex query using builder
     * $articles = $repository->query()
     *     ->where('status', 'published')
     *     ->where('published_at', '<=', now())
     *     ->orderBy('published_at', 'desc')
     *     ->limit(10)
     *     ->get();
     * ```
     */
    public function query(): Builder;

    /**
     * Set relationships to eager load on subsequent queries.
     *
     * Prevents N+1 query problems by specifying relationships to load
     * in advance. Applies to the next query operation.
     *
     * @param array<int, string>|string $relations Relationship name(s) to eager load
     *
     * @return static Returns self for method chaining
     *
     * @example
     * ```php
     * // Single relationship
     * $articles = $repository->with('author')->all();
     *
     * // Multiple relationships
     * $articles = $repository->with(['author', 'categories', 'tags'])->paginate();
     *
     * // Nested relationships
     * $articles = $repository->with(['author.profile', 'comments.user'])->find($id);
     * ```
     */
    public function with(array|string $relations): static;
}
