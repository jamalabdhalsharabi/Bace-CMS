<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Reject Comments Action.
 *
 * Rejects multiple comments in a single database operation.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkRejectCommentsAction extends Action
{
    /**
     * Execute the bulk reject action.
     *
     * @param array<string> $ids Array of comment IDs to reject
     *
     * @return int Number of comments rejected
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
    }
}
