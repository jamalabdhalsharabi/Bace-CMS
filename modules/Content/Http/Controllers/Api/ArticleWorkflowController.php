<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Application\Services\ArticleCommandService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Http\Requests\ScheduleArticleRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Workflow Controller.
 *
 * Handles all workflow-related operations for articles including
 * publishing, scheduling, review process, archiving, and pinning.
 * This controller follows the Single Responsibility Principle.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ArticleListingController For read operations
 * @see ArticleManagementController For CRUD operations
 */
final class ArticleWorkflowController extends BaseController
{
    /**
     * Create a new ArticleWorkflowController instance.
     *
     * @param ArticleQueryService $queryService Service for article read operations
     * @param ArticleCommandService $commandService Service for article write operations
     */
    public function __construct(
        private readonly ArticleQueryService $queryService,
        private readonly ArticleCommandService $commandService
    ) {}

    /**
     * Publish the specified article, making it publicly visible.
     *
     * @param string $id The UUID of the article to publish
     *
     * @return JsonResponse The published article wrapped in ArticleResource or 404 error
     *
     * @throws \Throwable When publishing fails
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->publish($article);

            return $this->success(new ArticleResource($article), 'Article published successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish article: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish a currently published article.
     *
     * @param string $id The UUID of the article to unpublish
     *
     * @return JsonResponse The unpublished article or 404 error
     *
     * @throws \Throwable When unpublishing fails
     */
    public function unpublish(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->unpublish($article);

            return $this->success(new ArticleResource($article), 'Article unpublished successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish article: ' . $e->getMessage());
        }
    }

    /**
     * Schedule an article for future publication.
     *
     * @param ScheduleArticleRequest $request The request containing 'publish_at' datetime
     * @param string $id The UUID of the article to schedule
     *
     * @return JsonResponse The scheduled article or 404 error
     *
     * @throws \Throwable When scheduling fails
     */
    public function schedule(ScheduleArticleRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->schedule(
                $article,
                Carbon::parse($request->validated()['publish_at'])
            );

            return $this->success(new ArticleResource($article), 'Article scheduled successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to schedule article: ' . $e->getMessage());
        }
    }

    /**
     * Submit the article for editorial review.
     *
     * @param string $id The UUID of the article to submit
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When submission fails
     */
    public function submitForReview(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->submitForReview($article);

            return $this->success(new ArticleResource($article), 'Article submitted for review');
        } catch (\Throwable $e) {
            return $this->error('Failed to submit article: ' . $e->getMessage());
        }
    }

    /**
     * Start the review process for an article.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When starting review fails
     */
    public function startReview(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->startReview($article);

            return $this->success(new ArticleResource($article), 'Review started');
        } catch (\Throwable $e) {
            return $this->error('Failed to start review: ' . $e->getMessage());
        }
    }

    /**
     * Approve an article that is pending review.
     *
     * @param Request $request The request containing optional approval notes
     * @param string $id The UUID of the article to approve
     *
     * @return JsonResponse The approved article or 404 error
     *
     * @throws \Throwable When approval fails
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->approve($article, $request->input('notes'));

            return $this->success(new ArticleResource($article), 'Article approved');
        } catch (\Throwable $e) {
            return $this->error('Failed to approve article: ' . $e->getMessage());
        }
    }

    /**
     * Reject an article that is pending review.
     *
     * @param Request $request The request containing rejection notes
     * @param string $id The UUID of the article to reject
     *
     * @return JsonResponse The rejected article or 404 error
     *
     * @throws \Throwable When rejection fails
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->reject($article, $request->input('notes'));

            return $this->success(new ArticleResource($article), 'Article rejected');
        } catch (\Throwable $e) {
            return $this->error('Failed to reject article: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified article.
     *
     * @param string $id The UUID of the article to archive
     *
     * @return JsonResponse The archived article or 404 error
     *
     * @throws \Throwable When archiving fails
     */
    public function archive(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->archive($article);

            return $this->success(new ArticleResource($article), 'Article archived');
        } catch (\Throwable $e) {
            return $this->error('Failed to archive article: ' . $e->getMessage());
        }
    }

    /**
     * Restore an archived article back to active status.
     *
     * @param string $id The UUID of the article to unarchive
     *
     * @return JsonResponse The restored article or 404 error
     *
     * @throws \Throwable When unarchiving fails
     */
    public function unarchive(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->unarchive($article);

            return $this->success(new ArticleResource($article), 'Article restored from archive');
        } catch (\Throwable $e) {
            return $this->error('Failed to unarchive article: ' . $e->getMessage());
        }
    }

    /**
     * Pin the article to the top of listings.
     *
     * @param string $id The UUID of the article to pin
     *
     * @return JsonResponse The pinned article or 404 error
     *
     * @throws \Throwable When pinning fails
     */
    public function pin(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->pin($article);

            return $this->success(new ArticleResource($article), 'Article pinned');
        } catch (\Throwable $e) {
            return $this->error('Failed to pin article: ' . $e->getMessage());
        }
    }

    /**
     * Remove the pin from the article.
     *
     * @param string $id The UUID of the article to unpin
     *
     * @return JsonResponse The unpinned article or 404 error
     *
     * @throws \Throwable When unpinning fails
     */
    public function unpin(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->unpin($article);

            return $this->success(new ArticleResource($article), 'Article unpinned');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpin article: ' . $e->getMessage());
        }
    }
}
