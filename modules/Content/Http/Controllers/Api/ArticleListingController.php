<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Listing Controller.
 *
 * Handles all read-only operations for articles including listing,
 * viewing, and retrieving article data. This controller follows
 * the Single Responsibility Principle by focusing only on query operations.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ArticleManagementController For create/update/delete operations
 * @see ArticleWorkflowController For publishing and workflow operations
 */
final class ArticleListingController extends BaseController
{
    /**
     * Create a new ArticleListingController instance.
     *
     * @param ArticleQueryService $queryService Service for article read operations
     */
    public function __construct(
        private readonly ArticleQueryService $queryService
    ) {}

    /**
     * Display a paginated listing of articles.
     *
     * Supports filtering by status, type, author, featured flag, and search term.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     *
     * @return JsonResponse Paginated list of articles wrapped in ArticleResource
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $articles = $this->queryService->list(
                filters: $request->only(['status', 'type', 'author_id', 'featured', 'search']),
                perPage: $request->integer('per_page', 15)
            );

            return $this->paginated(ArticleResource::collection($articles)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve articles: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified article by its UUID.
     *
     * @param string $id The UUID of the article to retrieve
     *
     * @return JsonResponse The article data wrapped in ArticleResource or 404 error
     */
    public function show(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            return $this->success(new ArticleResource($article));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve article: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified article by its URL slug.
     *
     * Also increments the article's view count for analytics.
     *
     * @param string $slug The URL-friendly slug of the article
     *
     * @return JsonResponse The article data wrapped in ArticleResource or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $article = $this->queryService->findBySlug($slug);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article->incrementViewCount();

            return $this->success(new ArticleResource($article));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve article: ' . $e->getMessage());
        }
    }

    /**
     * Get analytics data for the article.
     *
     * Returns view counts, engagement metrics, and other statistics.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse Analytics data or 404 error
     */
    public function analytics(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            return $this->success($this->queryService->getAnalytics($article));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve analytics: ' . $e->getMessage());
        }
    }

    /**
     * Get the revision history of the article.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse Array of revisions or 404 error
     */
    public function revisions(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            return $this->success($this->queryService->getRevisions($article));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve revisions: ' . $e->getMessage());
        }
    }
}
