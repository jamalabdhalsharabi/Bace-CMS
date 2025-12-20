<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Spam Comment Action.
 *
 * Handles marking comments as spam during moderation process.
 * Updates the comment status to 'spam' and sets spam-related flags
 * for filtering and training spam detection algorithms.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SpamCommentAction extends Action
{
    /**
     * Create a new SpamCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the spam marking action.
     *
     * Changes the comment status to 'spam' and sets the is_spam flag.
     * Spam comments are hidden from public view and can be used for
     * training machine learning spam detection models.
     *
     * @param Comment $comment The comment instance to mark as spam
     * 
     * @return Comment The freshly loaded spam-marked comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When update operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $this->repository->update($comment->id, [
            'status' => 'spam',
            'is_spam' => true,
        ]);

        return $comment->fresh();
    }
}
