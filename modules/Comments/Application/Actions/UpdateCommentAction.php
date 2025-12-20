<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Update Comment Action.
 *
 * Handles the modification of existing comment content and properties.
 * Allows users to edit their comments while maintaining audit trails
 * and preserving comment integrity within the threading system.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateCommentAction extends Action
{
    /**
     * Create a new UpdateCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment update action.
     *
     * Updates the specified comment with new data. Currently supports
     * content modification while preserving other comment attributes.
     * The update timestamp is automatically handled by the model.
     *
     * Authorization and validation should be performed at the controller
     * level before calling this action to ensure users can only edit
     * their own comments within allowed timeframes.
     *
     * @param Comment $comment The comment instance to update
     * @param array<string, mixed> $data Update data containing new content and properties
     * 
     * @return Comment The freshly loaded updated comment
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When update operation fails
     */
    public function execute(Comment $comment, array $data): Comment
    {
        $updateData = [
            'content' => $data['content'] ?? $comment->content,
            'edited_at' => now(),
        ];

        $this->repository->update($comment->id, $updateData);

        return $comment->fresh();
    }
}
