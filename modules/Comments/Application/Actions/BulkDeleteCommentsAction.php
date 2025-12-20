<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Delete Comments Action.
 *
 * Handles mass soft deletion of multiple comments in a single database operation.
 * Soft-deleted comments are hidden from public view but remain in the database
 * for potential restoration or audit purposes.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkDeleteCommentsAction extends Action
{
    /**
     * Execute the bulk comment deletion action.
     *
     * Soft deletes all specified comments in a single database query.
     * Deleted comments are marked with deleted_at timestamp but remain
     * in the database and can be restored if needed.
     *
     * This bulk operation is more efficient than individual deletions when
     * moderators need to remove many comments simultaneously, such as:
     * - Cleaning up spam or inappropriate content
     * - Removing comments from banned users
     * - Batch processing moderation decisions
     * - Clearing outdated or irrelevant discussions
     *
     * Note: This performs soft delete. Use ForceDeleteCommentAction for
     * permanent removal when required by data retention policies.
     *
     * @param array<string> $ids Array of comment UUIDs to soft delete
     *
     * @return int Number of comments successfully soft deleted
     * 
     * @throws \Illuminate\Database\QueryException When database update fails
     * @throws \Exception When bulk operation encounters an error
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->delete();
    }
}
