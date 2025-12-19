<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\Models\Currency;

/**
 * Set Default Currency Action.
 *
 * Sets a currency as the default currency.
 *
 * @package Modules\Currency\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SetDefaultCurrencyAction extends Action
{
    /**
     * Execute the set default action.
     *
     * @param Currency $currency The currency to set as default
     *
     * @return Currency The updated currency
     */
    public function execute(Currency $currency): Currency
    {
        // Remove default from all other currencies
        Currency::where('is_default', true)->update(['is_default' => false]);
        
        // Set this currency as default
        $currency->update(['is_default' => true]);

        return $currency->fresh();
    }
}
