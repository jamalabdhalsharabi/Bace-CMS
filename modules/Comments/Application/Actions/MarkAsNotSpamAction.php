<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Mark As Not Spam Action.
 *
 * Removes the spam flag from a comment and sets it back to pending review.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MarkAsNotSpamAction extends Action
{
    /**
     * Execute the mark as not spam action.
     *
     * @param Comment $comment The comment to unmark as spam
     *
     * @return Comment The updated comment
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update([
            'is_spam' => false,
            'status' => 'pending',
        ]);
        return $comment->fresh();
    }
}
