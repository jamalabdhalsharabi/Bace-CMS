<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Pin Comment Action.
 *
 * Handles pinning comments to the top of comment threads for prominence.
 * Pinned comments appear at the top of the comment list regardless of
 * chronological order, making important comments more visible to users.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PinCommentAction extends Action
{
    /**
     * Execute the comment pinning action.
     *
     * Marks the specified comment as pinned, causing it to appear prominently
     * at the top of comment threads. Pinned comments are displayed before
     * regular comments regardless of their creation date or vote count.
     *
     * Pinned comments are useful for:
     * - Highlighting important announcements or clarifications
     * - Featuring high-quality community responses
     * - Displaying official responses from content authors
     * - Showcasing exemplary community contributions
     *
     * Only moderators and content owners should have permission to pin comments.
     *
     * @param Comment $comment The comment instance to pin
     *
     * @return Comment The freshly loaded pinned comment with updated attributes
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When pinning operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update([
            'is_pinned' => true,
            'updated_at' => now(),
        ]);
        
        return $comment->fresh();
    }
}
