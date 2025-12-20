<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Events\CommentApproved;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Approve Comment Action.
 *
 * Handles the approval of a pending comment, making it visible to the public.
 * Updates the comment status to 'approved' and triggers the CommentApproved event
 * for any listeners that need to respond to comment approvals.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ApproveCommentAction extends Action
{
    /**
     * Create a new ApproveCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment approval action.
     *
     * Changes the comment status from 'pending' to 'approved', making it
     * visible to public users. Also triggers the CommentApproved event to
     * notify other parts of the system about the approval.
     *
     * @param Comment $comment The comment instance to approve
     * 
     * @return Comment The freshly loaded approved comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When update operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $this->repository->update($comment->id, ['status' => 'approved']);

        event(new CommentApproved($comment));

        return $comment->fresh();
    }
}
