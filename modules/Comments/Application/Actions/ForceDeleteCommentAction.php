<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Force Delete Comment Action.
 *
 * Handles the permanent removal of comments from the database, bypassing
 * the soft delete mechanism. This action is irreversible and should only
 * be used for confirmed spam, legal compliance, or data retention policies.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ForceDeleteCommentAction extends Action
{
    /**
     * Execute the permanent comment deletion action.
     *
     * Permanently removes the specified comment from the database, including
     * all related data and relationships. This operation bypasses Laravel's
     * soft delete mechanism and cannot be undone.
     *
     * Warning: This action permanently destroys data and should only be used
     * when absolutely necessary, such as:
     * - Confirmed spam content that poses security risks
     * - Legal compliance requirements (GDPR, right to be forgotten)
     * - Data retention policy enforcement
     *
     * @param Comment $comment The comment instance to permanently delete
     *
     * @return bool True if the permanent deletion was successful, false otherwise
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When permanent deletion fails due to database constraints
     */
    public function execute(Comment $comment): bool
    {
        return $comment->forceDelete();
    }
}
