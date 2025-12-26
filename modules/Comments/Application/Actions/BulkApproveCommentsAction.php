<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Approve Comments Action.
 *
 * Handles mass approval of multiple pending comments during moderation.
 * Efficiently updates multiple comment statuses in a single database operation
 * for improved performance when processing large batches of comments.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkApproveCommentsAction extends Action
{
    /**
     * Execute the bulk comment approval action.
     *
     * Updates the status of all specified comments to 'approved' in a single
     * database query. Approved comments become visible to the public and
     * participate in the normal comment display flow.
     *
     * This bulk operation is more efficient than individual approvals when
     * moderators need to process many comments simultaneously, such as:
     * - Clearing the moderation queue after reviewing multiple comments
     * - Approving all comments from trusted users
     * - Batch processing after spam filter review
     *
     * Note: Does not set approved_at or approved_by fields. Consider enhancing
     * if audit trail is required for bulk operations.
     *
     * @param array<string> $ids Array of comment UUIDs to approve
     *
     * @return int Number of comments successfully approved
     * 
     * @throws \Illuminate\Database\QueryException When database update fails
     * @throws \Exception When bulk operation encounters an error
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => request()->user()?->id,
            'updated_at' => now(),
        ]);
    }
}
