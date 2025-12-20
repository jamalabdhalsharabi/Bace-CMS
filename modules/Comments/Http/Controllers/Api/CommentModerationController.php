<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Comment Moderation Controller.
 *
 * Handles comment moderation operations including approval, rejection,
 * spam detection, hiding/showing, and pinning functionality.
 * Follows Single Responsibility Principle by focusing solely on moderation.
 *
 * @package Modules\Comments\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
class CommentModerationController extends BaseController
{
    /**
     * Create a new CommentModerationController instance.
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
     * Approve a pending comment.
     *
     * Sets the comment status to 'approved' making it visible to public.
     * Records approval timestamp and approving user for audit purposes.
     *
     * @param string $id The comment UUID to approve
     * 
     * @return JsonResponse Success response with approved comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);

            if (!$comment) {
                return $this->notFound('Comment not found');
            }

            $comment = $this->commandService->approve($comment);

            return $this->success(new CommentResource($comment), 'Comment approved');
        } catch (\Exception $e) {
            return $this->error('Failed to approve comment: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending comment.
     *
     * Sets the comment status to 'rejected' preventing public display.
     * Rejected comments remain in system for moderation review.
     *
     * @param string $id The comment UUID to reject
     * 
     * @return JsonResponse Success response with rejected comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function reject(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);

            if (!$comment) {
                return $this->notFound('Comment not found');
            }

            $comment = $this->commandService->reject($comment);

            return $this->success(new CommentResource($comment), 'Comment rejected');
        } catch (\Exception $e) {
            return $this->error('Failed to reject comment: ' . $e->getMessage());
        }
    }

    /**
     * Mark a comment as spam.
     *
     * Flags comment as spam and hides it from public view.
     * Spam comments can be used for training spam detection algorithms.
     *
     * @param string $id The comment UUID to mark as spam
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function spam(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->markAsSpam($comment);
            
            return $this->success(new CommentResource($comment), 'Comment marked as spam');
        } catch (\Exception $e) {
            return $this->error('Failed to mark comment as spam: ' . $e->getMessage());
        }
    }

    /**
     * Confirm comment is not spam.
     *
     * Removes spam flag from comment and restores normal status.
     * Used when spam detection produces false positives.
     *
     * @param string $id The comment UUID to mark as not spam
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function notSpam(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->markAsNotSpam($comment);
            
            return $this->success(new CommentResource($comment), 'Comment marked as not spam');
        } catch (\Exception $e) {
            return $this->error('Failed to mark comment as not spam: ' . $e->getMessage());
        }
    }

    /**
     * Hide a comment from public view.
     *
     * Temporarily hides comment without changing approval status.
     * Hidden comments can be unhidden later by moderators.
     *
     * @param string $id The comment UUID to hide
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function hide(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->hide($comment);
            
            return $this->success(new CommentResource($comment), 'Comment hidden');
        } catch (\Exception $e) {
            return $this->error('Failed to hide comment: ' . $e->getMessage());
        }
    }

    /**
     * Show a hidden comment.
     *
     * Makes a hidden comment visible to public again.
     * Restores previous status (approved/pending) from before hiding.
     *
     * @param string $id The comment UUID to unhide
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function unhide(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->unhide($comment);
            
            return $this->success(new CommentResource($comment), 'Comment visible');
        } catch (\Exception $e) {
            return $this->error('Failed to unhide comment: ' . $e->getMessage());
        }
    }

    /**
     * Pin a comment to the top of the list.
     *
     * Sets comment as pinned, making it appear prominently
     * at the top of comment threads regardless of chronological order.
     *
     * @param string $id The comment UUID to pin
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function pin(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->pin($comment);
            
            return $this->success(new CommentResource($comment), 'Comment pinned');
        } catch (\Exception $e) {
            return $this->error('Failed to pin comment: ' . $e->getMessage());
        }
    }

    /**
     * Unpin a comment.
     *
     * Removes pinned status from comment, returning it to
     * normal chronological ordering in comment threads.
     *
     * @param string $id The comment UUID to unpin
     * 
     * @return JsonResponse Success response with updated comment data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     */
    public function unpin(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->unpin($comment);
            
            return $this->success(new CommentResource($comment), 'Comment unpinned');
        } catch (\Exception $e) {
            return $this->error('Failed to unpin comment: ' . $e->getMessage());
        }
    }
}
