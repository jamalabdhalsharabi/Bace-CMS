<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unhide Comment Action.
 *
 * Makes a previously hidden comment visible again.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnhideCommentAction extends Action
{
    /**
     * Execute the unhide action.
     *
     * @param Comment $comment The comment to unhide
     *
     * @return Comment The updated comment
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => false]);
        return $comment->fresh();
    }
}
