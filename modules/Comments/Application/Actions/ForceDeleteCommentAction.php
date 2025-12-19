<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Force Delete Comment Action.
 *
 * Permanently removes a comment from the database.
 * This bypasses soft-delete and cannot be undone.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ForceDeleteCommentAction extends Action
{
    /**
     * Execute the force delete action.
     *
     * @param Comment $comment The comment to permanently delete
     *
     * @return bool True if deletion was successful
     */
    public function execute(Comment $comment): bool
    {
        return $comment->forceDelete();
    }
}
