<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

/**
 * Unfreeze Exchange Rate Action.
 *
 * Removes the frozen status from an exchange rate,
 * allowing it to be updated by automatic sync.
 *
 * @package Modules\ExchangeRates\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UnfreezeExchangeRateAction extends Action
{
    /**
     * Execute the unfreeze action.
     *
     * @param ExchangeRate $rate The exchange rate to unfreeze
     *
     * @return ExchangeRate The updated exchange rate
     */
    public function execute(ExchangeRate $rate): ExchangeRate
    {
        $rate->update(['is_frozen' => false]);
        return $rate->fresh();
    }
}
