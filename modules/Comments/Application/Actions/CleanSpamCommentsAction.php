<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Clean Spam Comments Action.
 *
 * Handles the permanent removal of old spam comments from the database.
 * This maintenance operation helps keep the database clean by removing
 * confirmed spam content that is no longer needed for training or analysis.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CleanSpamCommentsAction extends Action
{
    /**
     * Execute the spam cleanup action.
     *
     * Permanently deletes spam comments that are older than the specified
     * number of days. This is typically run as a scheduled maintenance task
     * to prevent the database from growing too large with unwanted content.
     *
     * Only comments marked as spam (is_spam = true) are eligible for deletion.
     * This operation bypasses soft delete and cannot be undone, so it should
     * only be used after spam detection has been confirmed.
     *
     * @param int $daysOld Delete spam comments older than this many days (default: 30)
     *
     * @return int Number of spam comments permanently deleted from database
     * 
     * @throws \Illuminate\Database\QueryException When database deletion fails
     * @throws \Exception When cleanup operation encounters an error
     */
    public function execute(int $daysOld = 30): int
    {
        return Comment::where('is_spam', true)
            ->where('status', 'spam')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->forceDelete();
    }
}
