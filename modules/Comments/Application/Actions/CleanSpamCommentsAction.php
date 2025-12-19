<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Clean Spam Comments Action.
 *
 * Permanently deletes old spam comments from the database.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CleanSpamCommentsAction extends Action
{
    /**
     * Execute the clean spam action.
     *
     * @param int $daysOld Delete spam comments older than this many days
     *
     * @return int Number of comments permanently deleted
     */
    public function execute(int $daysOld = 30): int
    {
        return Comment::where('is_spam', true)
            ->where('created_at', '<', now()->subDays($daysOld))
            ->forceDelete();
    }
}
