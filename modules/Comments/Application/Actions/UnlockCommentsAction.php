<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unlock Comments Action.
 *
 * Handles unlocking previously locked comments for a specific commentable entity.
 * Restores normal commenting functionality, allowing users to add replies,
 * edit comments, and participate in discussions again.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnlockCommentsAction extends Action
{
    /**
     * Execute the comment unlocking action.
     *
     * Unlocks all comments (root and replies) for a specific commentable entity
     * by setting the is_locked flag to false. This restores:
     * - Ability to add new replies
     * - Permission to edit existing comments (within edit window)
     * - Voting functionality on comments
     *
     * Common use cases:
     * - Reopening discussions after moderation review
     * - Restoring commenting on updated or corrected content
     * - Allowing continued discussion after temporary freeze
     * - Reversing accidental or incorrect lock operations
     *
     * @param string $modelType The fully qualified commentable model class name
     * @param string $modelId The UUID of the commentable model instance
     *
     * @return int Number of comments successfully unlocked
     * 
     * @throws \Illuminate\Database\QueryException When database update fails
     * @throws \Exception When unlock operation encounters an error
     */
    public function execute(string $modelType, string $modelId): int
    {
        return Comment::where('commentable_type', $modelType)
            ->where('commentable_id', $modelId)
            ->update([
                'is_locked' => false,
                'updated_at' => now(),
            ]);
    }
}
