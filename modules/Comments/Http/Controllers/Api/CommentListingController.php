<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Http\Requests\IndexCommentsRequest;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Comment Listing Controller.
 *
 * Handles read-only operations for comments including listing, searching,
 * and retrieving comment statistics. Follows Single Responsibility Principle
 * by focusing solely on data retrieval and presentation.
 *
 * @package Modules\Comments\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CommentListingController extends BaseController
{
    /**
     * Create a new CommentListingController instance.
     *
     * @param CommentQueryService $queryService Service for comment queries
     */
    public function __construct(
        private readonly CommentQueryService $queryService
    ) {}

    /**
     * Get comments for a specific commentable model.
     *
     * Retrieves paginated comments for any commentable entity (Article, Product, etc.)
     * with optional filtering and sorting capabilities.
     *
     * @param IndexCommentsRequest $request Validated request with commentable type/id
     * @return JsonResponse Paginated collection of comments
     *
     * @throws \Exception If commentable model is not found
     */
    public function index(IndexCommentsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $comments = $this->queryService->getForModel(
                $data['commentable_type'],
                $data['commentable_id'],
                $request->integer('per_page', 20)
            );

            return $this->paginated(CommentResource::collection($comments)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve comments: ' . $e->getMessage());
        }
    }

    /**
     * Get pending comments for moderation.
     *
     * Retrieves comments awaiting moderator approval in the admin interface.
     * Used by moderators to review and manage pending content.
     *
     * @param Request $request HTTP request with optional pagination parameters
     * @return JsonResponse Paginated collection of pending comments
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $comments = $this->queryService->getPending($request->integer('per_page', 20));

            return $this->paginated(CommentResource::collection($comments)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve pending comments: ' . $e->getMessage());
        }
    }

    /**
     * Get a single comment by ID.
     *
     * Retrieves detailed information for a specific comment including
     * author information and reply thread if applicable.
     *
     * @param string $id UUID of the comment to retrieve
     * @return JsonResponse Comment resource or not found error
     */
    public function show(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);

            if (!$comment) {
                return $this->notFound('Comment not found');
            }

            return $this->success(new CommentResource($comment));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve comment: ' . $e->getMessage());
        }
    }

    /**
     * Get comment statistics.
     *
     * Retrieves aggregated statistics for comments on a specific model
     * including counts by status, average ratings, and activity metrics.
     *
     * @param Request $request HTTP request with commentable_type and commentable_id
     * @return JsonResponse Comment statistics data
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = $this->queryService->getStats(
                $request->commentable_type,
                $request->commentable_id
            );

            return $this->success($stats);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve comment statistics: ' . $e->getMessage());
        }
    }
}
