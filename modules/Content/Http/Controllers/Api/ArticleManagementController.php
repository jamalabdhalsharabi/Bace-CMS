<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Application\Services\ArticleCommandService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Http\Requests\CreateArticleRequest;
use Modules\Content\Http\Requests\RestoreRevisionRequest;
use Modules\Content\Http\Requests\UpdateArticleRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Management Controller.
 *
 * Handles CRUD operations for articles including create, update, delete,
 * duplicate, and restore operations. This controller follows the Single
 * Responsibility Principle by focusing only on management operations.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ArticleListingController For read operations
 * @see ArticleWorkflowController For publishing and workflow operations
 */
final class ArticleManagementController extends BaseController
{
    /**
     * Create a new ArticleManagementController instance.
     *
     * @param ArticleQueryService $queryService Service for article read operations
     * @param ArticleCommandService $commandService Service for article write operations
     */
    public function __construct(
        private readonly ArticleQueryService $queryService,
        private readonly ArticleCommandService $commandService
    ) {}

    /**
     * Store a newly created article in the database.
     *
     * @param CreateArticleRequest $request The validated request containing article data
     *
     * @return JsonResponse The newly created article wrapped in ArticleResource (HTTP 201)
     *
     * @throws \Throwable When article creation fails
     */
    public function store(CreateArticleRequest $request): JsonResponse
    {
        try {
            $article = $this->commandService->create(
                ArticleData::fromArray($request->validated())
            );

            return $this->created(new ArticleResource($article), 'Article created successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to create article: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified article in the database.
     *
     * @param UpdateArticleRequest $request The validated request containing updated article data
     * @param string $id The UUID of the article to update
     *
     * @return JsonResponse The updated article wrapped in ArticleResource or 404 error
     *
     * @throws \Throwable When article update fails
     */
    public function update(UpdateArticleRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->update(
                $article,
                ArticleData::fromArray($request->validated())
            );

            return $this->success(new ArticleResource($article), 'Article updated successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to update article: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified article.
     *
     * The article will be moved to trash and can be restored later.
     *
     * @param string $id The UUID of the article to delete
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When article deletion fails
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $this->commandService->delete($article);

            return $this->success(null, 'Article deleted successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete article: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete the specified article from the database.
     *
     * @param string $id The UUID of the article to permanently delete
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When article force deletion fails
     */
    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->commandService->forceDelete($id);

            return $deleted
                ? $this->success(null, 'Article permanently deleted')
                : $this->notFound('Article not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to permanently delete article: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted article from the trash.
     *
     * @param string $id The UUID of the article to restore
     *
     * @return JsonResponse The restored article or 404 error
     *
     * @throws \Throwable When article restoration fails
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $article = $this->commandService->restore($id);

            return $article
                ? $this->success(new ArticleResource($article), 'Article restored successfully')
                : $this->notFound('Article not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore article: ' . $e->getMessage());
        }
    }

    /**
     * Create a duplicate copy of the article.
     *
     * @param string $id The UUID of the article to duplicate
     *
     * @return JsonResponse The duplicated article (HTTP 201) or 404 error
     *
     * @throws \Throwable When article duplication fails
     */
    public function duplicate(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $clone = $this->commandService->duplicate($article);

            return $this->created(new ArticleResource($clone), 'Article duplicated successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to duplicate article: ' . $e->getMessage());
        }
    }

    /**
     * Restore the article to a previous revision.
     *
     * @param RestoreRevisionRequest $request The request containing 'revision_number'
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The restored article or 404 error
     *
     * @throws \Throwable When revision restoration fails
     */
    public function restoreRevision(RestoreRevisionRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->restoreRevision(
                $article,
                $request->validated()['revision_number']
            );

            return $this->success(new ArticleResource($article), 'Revision restored successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore revision: ' . $e->getMessage());
        }
    }
}
