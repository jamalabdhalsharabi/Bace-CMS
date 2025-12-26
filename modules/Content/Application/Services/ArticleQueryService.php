<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;

/**
 * Article Query Service.
 *
 * Handles all read-only query operations for articles.
 * Follows CQRS pattern by separating queries from commands.
 *
 * @package Modules\Content\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleQueryService
{
    /**
     * Create a new ArticleQueryService instance.
     *
     * @param ArticleRepository $repository The article repository for data access
     */
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Get paginated articles with filters.
     *
     * @param array<string, mixed> $filters Filter criteria
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['author', 'featuredImage', 'translation'])
            ->getPaginated($filters, $perPage);
    }

    /**
     * Find article by ID.
     *
     * @param string $id Article UUID
     * @return Article|null
     */
    public function find(string $id): ?Article
    {
        return $this->repository
            ->with(['author', 'featuredImage', 'translations'])
            ->find($id);
    }

    /**
     * Find article by slug.
     *
     * @param string $slug URL slug
     * @param string|null $locale Locale code
     * @return Article|null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Article
    {
        $article = $this->repository
            ->with(['author', 'featuredImage', 'translations'])
            ->findBySlug($slug, $locale);

        return $article;
    }

    /**
     * Get published articles.
     *
     * @param int $limit Maximum number
     * @return Collection<int, Article>
     */
    public function getPublished(int $limit = 10): Collection
    {
        return $this->repository
            ->with(['author', 'featuredImage', 'translation'])
            ->getPublished($limit);
    }

    /**
     * Get featured articles.
     *
     * @param int $limit Maximum number
     * @return Collection<int, Article>
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return $this->repository
            ->with(['author', 'featuredImage', 'translation'])
            ->getFeatured($limit);
    }

    /**
     * Get articles by author.
     *
     * @param string $authorId Author UUID
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getByAuthor(string $authorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['featuredImage', 'translation'])
            ->getByAuthor($authorId, $perPage);
    }

    /**
     * Get trashed articles.
     *
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['author', 'translation'])
            ->getTrashed($perPage);
    }
}
