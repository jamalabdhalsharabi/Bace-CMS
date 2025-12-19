<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Article;
use Modules\Core\Domain\Contracts\RepositoryInterface;

/**
 * Article Repository Interface.
 *
 * Defines the contract for article-specific data access operations.
 * Extends the base RepositoryInterface with article-specific methods
 * for content management operations.
 *
 * This interface provides methods for:
 * - Retrieving published and featured articles
 * - Finding articles by slug (with locale support)
 * - Filtering articles by author, status, and type
 * - Managing soft-deleted articles
 *
 * @extends RepositoryInterface<Article>
 *
 * @package Modules\Content\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see \Modules\Content\Domain\Repositories\ArticleRepository Default implementation
 */
interface ArticleRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated articles with optional filters.
     *
     * Retrieves a paginated list of articles that can be filtered
     * by various criteria such as status, type, author, and search term.
     *
     * Supported filters:
     * - `status`: Article status (draft, published, archived)
     * - `type`: Article type (post, news, tutorial)
     * - `author_id`: UUID of the author
     * - `featured`: Boolean, filter featured articles only
     * - `search`: Search term for title/content
     *
     * @param array<string, mixed> $filters Associative array of filter criteria
     * @param int                  $perPage Number of items per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated articles with metadata
     *
     * @example
     * ```php
     * // Get all published articles
     * $articles = $repository->getPaginated(['status' => 'published']);
     *
     * // Search with multiple filters
     * $articles = $repository->getPaginated([
     *     'status' => 'published',
     *     'type' => 'news',
     *     'search' => 'Laravel',
     * ], 20);
     * ```
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an article by its URL slug.
     *
     * Searches for an article by its translated slug value.
     * Supports multi-language by accepting an optional locale parameter.
     *
     * @param string      $slug   The URL-friendly slug
     * @param string|null $locale Locale code (e.g., 'en', 'ar'). Defaults to current locale.
     *
     * @return Article|null The article if found, null otherwise
     *
     * @example
     * ```php
     * // Find by slug in current locale
     * $article = $repository->findBySlug('my-article-title');
     *
     * // Find by slug in specific locale
     * $article = $repository->findBySlug('my-article-title', 'ar');
     * ```
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Article;

    /**
     * Get published articles.
     *
     * Retrieves articles that are published and whose publish date
     * is in the past or null. Results are ordered by publish date descending.
     *
     * @param int $limit Maximum number of articles to retrieve (default: 10)
     *
     * @return Collection<int, Article> Collection of published articles
     *
     * @example
     * ```php
     * // Get latest 10 published articles
     * $articles = $repository->getPublished();
     *
     * // Get latest 5 published articles
     * $articles = $repository->getPublished(5);
     * ```
     */
    public function getPublished(int $limit = 10): Collection;

    /**
     * Get featured articles.
     *
     * Retrieves published articles that are marked as featured.
     * Useful for homepage highlights or sidebar widgets.
     *
     * @param int $limit Maximum number of articles to retrieve (default: 5)
     *
     * @return Collection<int, Article> Collection of featured articles
     *
     * @example
     * ```php
     * $featuredArticles = $repository->getFeatured(3);
     * ```
     */
    public function getFeatured(int $limit = 5): Collection;

    /**
     * Get articles by a specific author.
     *
     * Retrieves all articles written by a specific author,
     * ordered by creation date descending.
     *
     * @param string $authorId The author's UUID
     * @param int    $perPage  Number of items per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated articles by the author
     *
     * @example
     * ```php
     * $authorArticles = $repository->getByAuthor($userId, 10);
     * ```
     */
    public function getByAuthor(string $authorId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get soft-deleted (trashed) articles.
     *
     * Retrieves articles that have been soft-deleted,
     * ordered by deletion date descending.
     *
     * @param int $perPage Number of items per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated trashed articles
     *
     * @example
     * ```php
     * $trashedArticles = $repository->getTrashed();
     * ```
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator;

    /**
     * Restore a soft-deleted article.
     *
     * Restores a previously soft-deleted article back to active status.
     *
     * @param string $id The article's UUID
     *
     * @return Article|null The restored article, or null if not found
     *
     * @example
     * ```php
     * $restoredArticle = $repository->restore($articleId);
     * ```
     */
    public function restore(string $id): ?Article;

    /**
     * Permanently delete an article.
     *
     * Removes an article from the database permanently.
     * This action cannot be undone.
     *
     * @param string $id The article's UUID
     *
     * @return bool True if deletion was successful
     *
     * @example
     * ```php
     * if ($repository->forceDelete($articleId)) {
     *     // Article permanently deleted
     * }
     * ```
     */
    public function forceDelete(string $id): bool;
}
