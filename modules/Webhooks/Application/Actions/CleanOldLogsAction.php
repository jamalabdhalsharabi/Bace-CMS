<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\WebhookLog;

/**
 * Clean Old Logs Action.
 *
 * Removes old webhook log records.
 *
 * @package Modules\Webhooks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CleanOldLogsAction extends Action
{
    /**
     * Execute the cleanup action.
     *
     * @param int $days Number of days to keep records
     *
     * @return int Number of deleted records
     */
    public function execute(int $days = 30): int
    {
        return WebhookLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}
