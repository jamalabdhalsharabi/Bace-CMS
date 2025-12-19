<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Pin Comment Action.
 *
 * Pins a comment to appear at the top of the comment list.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PinCommentAction extends Action
{
    /**
     * Execute the pin action.
     *
     * @param Comment $comment The comment to pin
     *
     * @return Comment The updated comment
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => true]);
        return $comment->fresh();
    }
}
