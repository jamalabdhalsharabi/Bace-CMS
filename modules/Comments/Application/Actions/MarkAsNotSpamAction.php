<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Mark As Not Spam Action.
 *
 * Handles the reversal of spam marking for false positive detections.
 * Removes spam flags from comments and restores them to pending status
 * for proper moderation review, improving spam detection accuracy over time.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MarkAsNotSpamAction extends Action
{
    /**
     * Execute the spam reversal action.
     *
     * Removes the spam designation from a comment that was incorrectly
     * flagged as spam. This action clears the is_spam flag and resets
     * the comment status to 'pending' for proper moderation review.
     *
     * This operation is typically performed when:
     * - Automated spam detection produces false positives
     * - Manual review determines content is legitimate
     * - Users appeal spam classifications through reporting systems
     * - Machine learning models need training data corrections
     *
     * After unmarking, the comment enters the normal moderation workflow
     * where it can be properly approved or rejected based on content quality.
     *
     * @param Comment $comment The comment instance to remove spam marking from
     *
     * @return Comment The freshly loaded comment with updated spam status
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment is not found
     * @throws \Exception When spam unmarking operation fails
     */
    public function execute(Comment $comment): Comment
    {
        $comment->update([
            'is_spam' => false,
            'status' => 'pending',
            'spam_score' => null,
            'updated_at' => now(),
        ]);
        
        return $comment->fresh();
    }
}
