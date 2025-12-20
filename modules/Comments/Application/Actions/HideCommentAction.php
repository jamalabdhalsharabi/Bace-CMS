<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Hide Comment Action.
 *
 * Handles temporarily hiding comments from public view without changing
 * their approval status. Hidden comments remain in the database and can
 * be unhidden later by moderators, making this a reversible moderation action.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class HideCommentAction extends Action
{
    /**
     * Execute the comment hiding action.
     *
     * Temporarily hides the specified comment from public view by setting
     * the status to 'hidden'. This is a reversible moderation action that
     * allows comments to be temporarily removed without permanent deletion
     * or status change.
     *
     * Hidden comments:
     * - Are not displayed in public comment threads
     * - Remain in the database for potential restoration
     * - Preserve their original approval status
     * - Can be unhidden by moderators at any time
     *
     * @param Comment $comment The comment instance to hide
     *
     * @return Comment The freshly loaded hidden comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When hiding operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update([
            'status' => 'hidden',
            'updated_at' => now(),
        ]);
        
        return $comment->fresh();
    }
}
