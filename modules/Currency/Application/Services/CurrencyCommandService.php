<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Currency\Application\Actions\CreateCurrencyAction;
use Modules\Currency\Application\Actions\DeleteCurrencyAction;
use Modules\Currency\Application\Actions\SetDefaultCurrencyAction;
use Modules\Currency\Application\Actions\UpdateCurrencyAction;
use Modules\Currency\Application\Actions\UpdateExchangeRateAction;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;

/**
 * Currency Command Service.
 *
 * Orchestrates all write operations for currencies via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Currency\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CurrencyCommandService
{
    /**
     * Create a new CurrencyCommandService instance.
     *
     * @param CreateCurrencyAction $createAction Action for creating currencies
     * @param UpdateCurrencyAction $updateAction Action for updating currencies
     * @param DeleteCurrencyAction $deleteAction Action for deleting currencies
     * @param SetDefaultCurrencyAction $setDefaultAction Action for setting default currency
     * @param UpdateExchangeRateAction $updateExchangeRateAction Action for updating exchange rates
     */
    public function __construct(
        private readonly CreateCurrencyAction $createAction,
        private readonly UpdateCurrencyAction $updateAction,
        private readonly DeleteCurrencyAction $deleteAction,
        private readonly SetDefaultCurrencyAction $setDefaultAction,
        private readonly UpdateExchangeRateAction $updateExchangeRateAction,
    ) {}

    /**
     * Create a new currency.
     *
     * @param CurrencyData $data The currency data DTO
     *
     * @return Currency The created currency
     */
    public function create(CurrencyData $data): Currency
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing currency.
     *
     * @param Currency $currency The currency to update
     * @param CurrencyData $data The updated currency data
     *
     * @return Currency The updated currency
     */
    public function update(Currency $currency, CurrencyData $data): Currency
    {
        return $this->updateAction->execute($currency, $data);
    }

    /**
     * Delete a currency.
     *
     * @param Currency $currency The currency to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Currency $currency): bool
    {
        return $this->deleteAction->execute($currency);
    }

    /**
     * Set a currency as the default.
     *
     * @param Currency $currency The currency to set as default
     *
     * @return Currency The updated currency
     */
    public function setDefault(Currency $currency): Currency
    {
        return $this->setDefaultAction->execute($currency);
    }

    /**
     * Update the exchange rate for a currency.
     *
     * @param Currency $currency The currency to update
     * @param float $rate The new exchange rate
     *
     * @return Currency The updated currency
     */
    public function updateExchangeRate(Currency $currency, float $rate): Currency
    {
        return $this->updateExchangeRateAction->execute($currency, $rate);
    }
}
