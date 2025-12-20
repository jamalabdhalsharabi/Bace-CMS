<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Lock Comments Action.
 *
 * Handles locking all comments associated with a specific commentable entity.
 * When comments are locked, users cannot add new replies or modify existing
 * comments, effectively freezing the discussion thread.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class LockCommentsAction extends Action
{
    /**
     * Execute the comment locking action.
     *
     * Locks all comments (root and replies) for a specific commentable entity
     * by setting the is_locked flag to true. This prevents:
     * - New replies from being added
     * - Existing comments from being edited
     * - Voting on locked comments
     *
     * Common use cases:
     * - Closing discussions that have become off-topic or heated
     * - Preventing comments on archived or outdated content
     * - Temporarily freezing discussions during moderation review
     * - Enforcing discussion deadlines or time limits
     *
     * @param string $modelType The fully qualified commentable model class name
     * @param string $modelId The UUID of the commentable model instance
     *
     * @return int Number of comments successfully locked
     * 
     * @throws \Illuminate\Database\QueryException When database update fails
     * @throws \Exception When lock operation encounters an error
     */
    public function execute(string $modelType, string $modelId): int
    {
        return Comment::where('commentable_type', $modelType)
            ->where('commentable_id', $modelId)
            ->update([
                'is_locked' => true,
                'updated_at' => now(),
            ]);
    }
}
