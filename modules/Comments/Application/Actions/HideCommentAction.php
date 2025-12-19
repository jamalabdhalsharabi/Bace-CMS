<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Hide Comment Action.
 *
 * Hides a comment from public view while keeping it in the database.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class HideCommentAction extends Action
{
    /**
     * Execute the hide action.
     *
     * @param Comment $comment The comment to hide
     *
     * @return Comment The updated comment
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => true]);
        return $comment->fresh();
    }
}
