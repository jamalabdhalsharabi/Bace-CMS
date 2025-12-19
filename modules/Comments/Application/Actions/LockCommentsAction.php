<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Lock Comments Action.
 *
 * Locks all comments for a specific commentable entity,
 * preventing new replies and modifications.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class LockCommentsAction extends Action
{
    /**
     * Execute the lock comments action.
     *
     * @param string $modelType The commentable model type
     * @param string $modelId The commentable model ID
     *
     * @return int Number of comments locked
     */
    public function execute(string $modelType, string $modelId): int
    {
        return Comment::where('commentable_type', $modelType)
            ->where('commentable_id', $modelId)
            ->update(['is_locked' => true]);
    }
}
