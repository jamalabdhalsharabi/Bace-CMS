<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Bulk Delete Comments Action.
 *
 * Soft-deletes multiple comments in a single database operation.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class BulkDeleteCommentsAction extends Action
{
    /**
     * Execute the bulk delete action.
     *
     * @param array<string> $ids Array of comment IDs to delete
     *
     * @return int Number of comments deleted
     */
    public function execute(array $ids): int
    {
        return Comment::whereIn('id', $ids)->delete();
    }
}
