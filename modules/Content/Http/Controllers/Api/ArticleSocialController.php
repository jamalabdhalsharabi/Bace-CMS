<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Application\Services\ArticleCommandService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Http\Requests\ShareOnSocialRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Social Controller.
 *
 * Handles social media and newsletter operations for articles.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleSocialController extends BaseController
{
    /**
     * Create a new ArticleSocialController instance.
     *
     * @param ArticleQueryService $queryService Service for article read operations
     * @param ArticleCommandService $commandService Service for article write operations
     */
    public function __construct(
        private readonly ArticleQueryService $queryService,
        private readonly ArticleCommandService $commandService
    ) {}

    /**
     * Share the article on social media platforms.
     *
     * @param ShareOnSocialRequest $request The request containing 'platforms' array
     * @param string $id The UUID of the article
     *
     * @return JsonResponse Share results or 404 error
     *
     * @throws \Throwable When sharing fails
     */
    public function shareOnSocial(ShareOnSocialRequest $request, string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $result = $this->commandService->shareOnSocial(
                $article,
                $request->validated()['platforms']
            );

            return $this->success($result, 'Article shared successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to share article: ' . $e->getMessage());
        }
    }

    /**
     * Queue the article to be sent to newsletter subscribers.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When newsletter queuing fails
     */
    public function sendToNewsletter(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $this->commandService->sendToNewsletter($article);

            return $this->success(null, 'Queued for newsletter');
        } catch (\Throwable $e) {
            return $this->error('Failed to queue for newsletter: ' . $e->getMessage());
        }
    }

    /**
     * Enable comments on the article.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When enabling comments fails
     */
    public function enableComments(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->enableComments($article);

            return $this->success(new ArticleResource($article), 'Comments enabled');
        } catch (\Throwable $e) {
            return $this->error('Failed to enable comments: ' . $e->getMessage());
        }
    }

    /**
     * Disable comments on the article.
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When disabling comments fails
     */
    public function disableComments(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->disableComments($article);

            return $this->success(new ArticleResource($article), 'Comments disabled');
        } catch (\Throwable $e) {
            return $this->error('Failed to disable comments: ' . $e->getMessage());
        }
    }

    /**
     * Close comments on the article (no new comments allowed).
     *
     * @param string $id The UUID of the article
     *
     * @return JsonResponse The updated article or 404 error
     *
     * @throws \Throwable When closing comments fails
     */
    public function closeComments(string $id): JsonResponse
    {
        try {
            $article = $this->queryService->find($id);

            if (!$article) {
                return $this->notFound('Article not found');
            }

            $article = $this->commandService->closeComments($article);

            return $this->success(new ArticleResource($article), 'Comments closed');
        } catch (\Throwable $e) {
            return $this->error('Failed to close comments: ' . $e->getMessage());
        }
    }
}
