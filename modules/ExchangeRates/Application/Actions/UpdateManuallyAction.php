<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

/**
 * Update Manually Action.
 *
 * Manually updates an exchange rate value.
 *
 * @package Modules\ExchangeRates\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateManuallyAction extends Action
{
    /**
     * Execute the manual update action.
     *
     * @param ExchangeRate $rate The exchange rate to update
     * @param float $newRate The new rate value
     *
     * @return ExchangeRate The updated exchange rate
     */
    public function execute(ExchangeRate $rate, float $newRate): ExchangeRate
    {
        $rate->update([
            'rate' => $newRate,
            'updated_at' => now(),
        ]);
        return $rate->fresh();
    }
}
