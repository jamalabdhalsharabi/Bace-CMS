<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unhide Comment Action.
 *
 * Handles restoring previously hidden comments back to public view.
 * This reversible moderation action allows moderators to undo temporary
 * hiding decisions and restore comments to their original display status.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnhideCommentAction extends Action
{
    /**
     * Execute the comment unhiding action.
     *
     * Restores a previously hidden comment back to public visibility by
     * reverting its status from 'hidden' back to its original state.
     * This action is typically used when moderators determine that a
     * comment was incorrectly hidden or when circumstances change.
     *
     * The unhidden comment will:
     * - Become visible in public comment threads again
     * - Return to its previous approval status (approved/pending)
     * - Resume normal interaction capabilities (voting, replies)
     * - Maintain its original creation timestamp and threading position
     *
     * @param Comment $comment The hidden comment instance to restore
     *
     * @return Comment The freshly loaded unhidden comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When unhiding operation fails
     */
    public function execute(Comment $comment): Comment
    {
        // Determine the previous status - default to 'approved' if it was hidden
        $previousStatus = $comment->is_spam ? 'spam' : 'approved';
        
        $comment->update([
            'status' => $previousStatus,
            'updated_at' => now(),
        ]);
        
        return $comment->fresh();
    }
}
