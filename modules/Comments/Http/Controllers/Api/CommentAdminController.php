<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Http\Requests\BanUserRequest;
use Modules\Comments\Http\Requests\LockCommentsRequest;
use Modules\Comments\Http\Requests\UnbanUserRequest;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Comment Administration Controller.
 *
 * Handles administrative operations including user management (banning/unbanning),
 * content locking/unlocking, and comment statistics.
 * Follows Single Responsibility Principle by focusing solely on admin operations.
 *
 * @package Modules\Comments\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
class CommentAdminController extends BaseController
{
    /**
     * Create a new CommentAdminController instance.
     *
     * @param CommentQueryService $queryService Service for querying comments
     * @param CommentCommandService $commandService Service for executing comment commands
     */
    public function __construct(
        protected CommentQueryService $queryService,
        protected CommentCommandService $commandService
    ) {
    }

    /**
     * Lock comments on specific content.
     *
     * Prevents new comments from being posted on the specified content.
     * Existing comments remain visible but no new ones can be added.
     *
     * @param LockCommentsRequest $request Validated request containing model type and ID
     * 
     * @return JsonResponse Success response confirming comments are locked
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function lockComments(LockCommentsRequest $request): JsonResponse
    {
        try {
            $this->commandService->lockComments(
                $request->model_type, 
                $request->model_id
            );
            
            return $this->success(null, 'Comments locked successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to lock comments: ' . $e->getMessage());
        }
    }

    /**
     * Unlock comments on specific content.
     *
     * Re-enables comment posting on the specified content.
     * Users can again submit new comments on the content.
     *
     * @param LockCommentsRequest $request Validated request containing model type and ID
     * 
     * @return JsonResponse Success response confirming comments are unlocked
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function unlockComments(LockCommentsRequest $request): JsonResponse
    {
        try {
            $this->commandService->unlockComments(
                $request->model_type, 
                $request->model_id
            );
            
            return $this->success(null, 'Comments unlocked successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to unlock comments: ' . $e->getMessage());
        }
    }

    /**
     * Ban a user from commenting.
     *
     * Prevents the specified user from posting new comments.
     * Can include reason and duration for the ban.
     *
     * @param BanUserRequest $request Validated request containing user ID, reason, and duration
     * 
     * @return JsonResponse Success response confirming user is banned
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function banUser(BanUserRequest $request): JsonResponse
    {
        try {
            $this->commandService->banUser(
                $request->user_id, 
                $request->reason, 
                $request->duration
            );
            
            return $this->success(null, 'User banned from commenting successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user.
     *
     * Removes commenting restrictions from the specified user.
     * User regains ability to post comments.
     *
     * @param UnbanUserRequest $request Validated request containing user ID
     * 
     * @return JsonResponse Success response confirming user is unbanned
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function unbanUser(UnbanUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->commandService->unbanUser($validated['user_id']);
            
            return $this->success(null, 'User unbanned successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Get comment statistics.
     *
     * Retrieves comprehensive statistics about comments including
     * total count, approved/pending/spam breakdown, and optional
     * filtering by specific commentable entity.
     *
     * @param Request $request Request containing optional commentable_type and commentable_id
     * 
     * @return JsonResponse Success response with comment statistics
     * @throws \Exception When statistics retrieval fails
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = $this->queryService->getStats(
                $request->commentable_type,
                $request->commentable_id
            );
            
            return $this->success($stats, 'Comment statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get pending comments for moderation queue.
     *
     * Retrieves paginated list of comments awaiting moderation approval.
     * Used by administrators to review and moderate user submissions.
     *
     * @param Request $request Request containing optional pagination parameters
     * 
     * @return JsonResponse Paginated response with pending comments
     * @throws \Exception When pending comments retrieval fails
     */
    public function getPendingComments(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 20);
            $comments = $this->queryService->getPending($perPage);
            
            return $this->paginated($comments, 'Pending comments retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve pending comments: ' . $e->getMessage());
        }
    }
}
