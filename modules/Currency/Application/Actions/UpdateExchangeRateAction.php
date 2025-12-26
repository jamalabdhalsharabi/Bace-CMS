<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Update Exchange Rate Action.
 *
 * Handles updating exchange rates for currencies.
 *
 * @package Modules\Currency\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateExchangeRateAction extends Action
{
    /**
     * Create a new UpdateExchangeRateAction instance.
     *
     * @param CurrencyRepository $repository The currency repository
     */
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    /**
     * Execute the exchange rate update action.
     *
     * @param Currency $currency The currency instance
     * @param float $rate The new exchange rate
     * 
     * @return Currency The updated currency
     * 
     * @throws \Exception When update fails
     */
    public function execute(Currency $currency, float $rate): Currency
    {
        $this->repository->update($currency->id, ['exchange_rate' => $rate]);

        return $currency->fresh();
    }
}
