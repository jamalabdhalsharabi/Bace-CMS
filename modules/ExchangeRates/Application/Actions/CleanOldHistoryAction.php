<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;

/**
 * Clean Old History Action.
 *
 * Removes old exchange rate history records.
 *
 * @package Modules\ExchangeRates\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CleanOldHistoryAction extends Action
{
    /**
     * Execute the cleanup action.
     *
     * @param int $days Number of days to keep records
     *
     * @return int Number of deleted records
     */
    public function execute(int $days = 365): int
    {
        return ExchangeRateHistory::where('recorded_at', '<', now()->subDays($days))->delete();
    }
}
