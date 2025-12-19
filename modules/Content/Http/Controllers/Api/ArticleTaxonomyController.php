<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Application\Services\ArticleTaxonomyService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Http\Requests\AddTagsRequest;
use Modules\Content\Http\Requests\AttachRelatedRequest;
use Modules\Content\Http\Requests\RemoveTagsRequest;
use Modules\Content\Http\Requests\SyncCategoriesRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Taxonomy Controller.
 *
 * Handles taxonomy-related operations for articles including
 * categories, tags, and related articles management.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleTaxonomyController extends BaseController
{
    /**
     * Create a new ArticleTaxonomyController instance.
     *
     * @param ArticleQueryService $queryService Service for article read operations
     * @param ArticleTaxonomyService $taxonomyService Service for taxonomy operations
     */
    public function __construct(
        private readonly ArticleQueryService $queryService,
        private readonly ArticleTaxonomyService $taxonomyService
    ) {}

    /**
     * Sync categories for the article.
     *
     * @param SyncCategoriesRequest $request The request containing 'category_ids' array
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When sync fails
     */
    public function syncCategories(SyncCategoriesRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->taxonomyService->syncCategories(
                $article,
                $request->validated()['category_ids']
            );

            return $this->success(new ArticleResource($article), 'Categories synced');
        } catch (\Throwable $e) {
            return $this->error('Failed to sync categories: ' . $e->getMessage());
        }
    }

    /**
     * Add tags to the article.
     *
     * @param AddTagsRequest $request The request containing 'tags' array
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When adding tags fails
     */
    public function addTags(AddTagsRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->taxonomyService->addTags(
                $article,
                $request->validated()['tags']
            );

            return $this->success(new ArticleResource($article), 'Tags added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add tags: ' . $e->getMessage());
        }
    }

    /**
     * Remove tags from the article.
     *
     * @param RemoveTagsRequest $request The request containing 'tag_ids' array
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When removing tags fails
     */
    public function removeTags(RemoveTagsRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->taxonomyService->removeTags(
                $article,
                $request->validated()['tag_ids']
            );

            return $this->success(new ArticleResource($article), 'Tags removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove tags: ' . $e->getMessage());
        }
    }

    /**
     * Attach related articles to this article.
     *
     * @param AttachRelatedRequest $request The request containing 'article_ids' array
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When attaching fails
     */
    public function attachRelated(AttachRelatedRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->taxonomyService->attachRelated(
                $article,
                $request->validated()['article_ids']
            );

            return $this->success(new ArticleResource($article), 'Related articles attached');
        } catch (\Throwable $e) {
            return $this->error('Failed to attach related articles: ' . $e->getMessage());
        }
    }
}
