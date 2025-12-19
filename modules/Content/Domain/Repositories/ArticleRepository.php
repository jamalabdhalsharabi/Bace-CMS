<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Contracts\ArticleRepositoryInterface;
use Modules\Content\Domain\Models\Article;
use Modules\Core\Domain\Repositories\BaseRepository;

/**
 * Article Repository Implementation.
 *
 * Read-only repository for Article model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * Features:
 * - Supports multi-language article lookups via slug
 * - Provides filtering by status, type, author, and search
 * - Uses eager loading to minimize N+1 queries
 *
 * @extends BaseRepository<Article>
 * @implements ArticleRepositoryInterface
 *
 * @package Modules\Content\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    /**
     * Create a new ArticleRepository instance.
     *
     * @param Article $model The Article model instance
     */
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated articles with filters.
     *
     * @param array<string, mixed> $filters Filter criteria
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
            );
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find article by slug.
     *
     * @param string $slug The URL slug
     * @param string|null $locale Locale code
     * @return Article|null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Article
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }

    /**
     * Get published articles.
     *
     * @param int $limit Maximum number of articles
     * @return Collection<int, Article>
     */
    public function getPublished(int $limit = 10): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured articles.
     *
     * @param int $limit Maximum number of articles
     * @return Collection<int, Article>
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit($limit)
            ->get();
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
        return $this->query()
            ->where('author_id', $authorId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get trashed articles.
     *
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->onlyTrashed()
            ->with($this->with)
            ->latest('deleted_at')
            ->paginate($perPage);
    }

    /**
     * Restore a trashed article.
     *
     * @param string $id Article UUID
     * @return Article|null
     */
    public function restore(string $id): ?Article
    {
        $article = $this->model->newQuery()->withTrashed()->find($id);
        $article?->restore();

        return $article;
    }

    /**
     * Force delete an article.
     *
     * @param string $id Article UUID
     * @return bool
     */
    public function forceDelete(string $id): bool
    {
        $article = $this->model->newQuery()->withTrashed()->find($id);

        return $article?->forceDelete() ?? false;
    }
}
