<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Approve Comments Action.
 *
 * Approves multiple comments in a single database operation.
 * This action contains the complete business logic for bulk approval.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkApproveCommentsAction extends Action
{
    /**
     * Execute the bulk approve action.
     *
     * @param array<string> $ids Array of comment IDs to approve
     *
     * @return int Number of comments approved
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'approved']);
    }
}
