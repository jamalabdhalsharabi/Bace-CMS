<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unpin Comment Action.
 *
 * Removes the pinned status from a comment.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnpinCommentAction extends Action
{
    /**
     * Execute the unpin action.
     *
     * @param Comment $comment The comment to unpin
     *
     * @return Comment The updated comment
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => false]);
        return $comment->fresh();
    }
}
