<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

/**
 * Update Or Create Rate Action.
 *
 * Creates a new exchange rate or updates an existing one.
 *
 * @package Modules\ExchangeRates\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateOrCreateRateAction extends Action
{
    /**
     * Execute the update or create action.
     *
     * @param string $baseId The base currency ID
     * @param string $targetId The target currency ID
     * @param array<string, mixed> $data The rate data
     *
     * @return ExchangeRate The created or updated exchange rate
     */
    public function execute(string $baseId, string $targetId, array $data): ExchangeRate
    {
        return ExchangeRate::updateOrCreate(
            ['base_currency_id' => $baseId, 'target_currency_id' => $targetId],
            $data
        );
    }
}
