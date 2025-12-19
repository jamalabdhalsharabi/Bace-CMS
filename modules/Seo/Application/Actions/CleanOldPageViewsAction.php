<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\PageView;

/**
 * Clean Old Page Views Action.
 *
 * Removes old page view records from the database.
 *
 * @package Modules\Seo\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CleanOldPageViewsAction extends Action
{
    /**
     * Execute the cleanup action.
     *
     * @param int $days Number of days to keep records
     *
     * @return int Number of deleted records
     */
    public function execute(int $days = 90): int
    {
        return PageView::where('viewed_at', '<', now()->subDays($days))->delete();
    }
}
