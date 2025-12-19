<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Unlock Comments Action.
 *
 * Unlocks all comments for a specific commentable entity.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnlockCommentsAction extends Action
{
    /**
     * Execute the unlock comments action.
     *
     * @param string $modelType The commentable model type
     * @param string $modelId The commentable model ID
     *
     * @return int Number of comments unlocked
     */
    public function execute(string $modelType, string $modelId): int
    {
        return Comment::where('commentable_type', $modelType)
            ->where('commentable_id', $modelId)
            ->update(['is_locked' => false]);
    }
}
