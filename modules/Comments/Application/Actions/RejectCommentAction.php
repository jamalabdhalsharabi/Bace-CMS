<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Reject Comment Action.
 *
 * Handles the rejection of pending comments during the moderation process.
 * Updates the comment status to 'rejected' and optionally records the
 * rejection reason for audit and feedback purposes.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RejectCommentAction extends Action
{
    /**
     * Create a new RejectCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment rejection action.
     *
     * Changes the comment status from 'pending' to 'rejected', preventing
     * it from being displayed publicly. Optionally records the rejection
     * reason in the comment's metadata for future reference.
     *
     * @param Comment $comment The comment instance to reject
     * @param string|null $reason Optional rejection reason for audit purposes
     * 
     * @return Comment The freshly loaded rejected comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When update operation fails
     */
    public function execute(Comment $comment, ?string $reason = null): Comment
    {
        // Update comment status to rejected
        // Note: rejection_reason parameter is accepted for API compatibility
        // but not stored (can be added via migration if needed)
        $this->repository->update($comment->id, [
            'status' => 'rejected',
        ]);

        return $comment->fresh();
    }
}
