<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Reject Comments Action.
 *
 * Handles the mass rejection of multiple pending comments during moderation.
 * Efficiently updates multiple comment statuses in a single database operation
 * for improved performance when processing large batches of comments.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkRejectCommentsAction extends Action
{
    /**
     * Execute the bulk comment rejection action.
     *
     * Updates the status of all specified comments to 'rejected' in a single
     * database query. Rejected comments are hidden from public view but remain
     * in the system for audit purposes and potential future review.
     *
     * This bulk operation is more efficient than individual rejections when
     * moderators need to process many comments simultaneously.
     *
     * @param array<string> $ids Array of comment UUIDs to reject
     *
     * @return int Number of comments successfully rejected
     * 
     * @throws \Illuminate\Database\QueryException When database update fails
     * @throws \Exception When bulk operation encounters an error
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update([
            'status' => 'rejected',
            'updated_at' => now(),
        ]);
    }
}
