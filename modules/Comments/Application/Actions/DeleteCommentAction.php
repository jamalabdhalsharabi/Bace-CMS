<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Delete Comment Action.
 *
 * Handles the soft deletion of comments from the system.
 * Comments are not permanently removed but marked as deleted,
 * allowing for potential recovery if needed.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeleteCommentAction extends Action
{
    /**
     * Create a new DeleteCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment deletion action.
     *
     * Performs a soft delete on the specified comment, marking it as deleted
     * in the database without permanently removing the record. This allows
     * for comment recovery if needed and maintains referential integrity.
     *
     * @param Comment $comment The comment instance to delete
     * 
     * @return bool True if deletion was successful, false otherwise
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When deletion operation fails
     */
    public function execute(Comment $comment): bool
    {
        return $this->repository->delete($comment->id);
    }
}
