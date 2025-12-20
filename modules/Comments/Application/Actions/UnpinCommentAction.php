<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unpin Comment Action.
 *
 * Handles removing the pinned status from comments, returning them to
 * normal chronological ordering within comment threads. This reversible
 * moderation action allows flexibility in comment thread management.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnpinCommentAction extends Action
{
    /**
     * Execute the comment unpinning action.
     *
     * Removes the pinned status from a previously pinned comment, causing
     * it to return to normal chronological ordering within the comment thread.
     * The comment will no longer appear at the top but will be displayed
     * according to its creation date and other sorting criteria.
     *
     * This action is useful when:
     * - Pinned announcements become outdated or irrelevant
     * - Comment threads need reorganization
     * - Temporary highlighting is no longer needed
     * - Rotating featured comments to give others prominence
     *
     * The unpinned comment maintains all other properties and remains
     * fully functional for voting, replies, and other interactions.
     *
     * @param Comment $comment The pinned comment instance to unpin
     *
     * @return Comment The freshly loaded unpinned comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When unpinning operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update([
            'is_pinned' => false,
            'updated_at' => now(),
        ]);
        
        return $comment->fresh();
    }
}
