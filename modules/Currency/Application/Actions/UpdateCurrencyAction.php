<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Update Currency Action.
 *
 * Handles updating existing currencies with automatic default currency management.
 *
 * @package Modules\Currency\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateCurrencyAction extends Action
{
    /**
     * Create a new UpdateCurrencyAction instance.
     *
     * @param CurrencyRepository $repository The currency repository
     */
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    /**
     * Execute the currency update action.
     *
     * @param Currency $currency The currency instance to update
     * @param CurrencyData $data The validated currency data
     * 
     * @return Currency The updated currency
     * 
     * @throws \Exception When update fails
     */
    public function execute(Currency $currency, CurrencyData $data): Currency
    {
        if ($data->is_default && !$currency->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $this->repository->update($currency->id, [
            'name' => $data->name,
            'symbol' => $data->symbol,
            'decimal_places' => $data->decimal_places,
            'is_active' => $data->is_active,
            'is_default' => $data->is_default,
            'exchange_rate' => $data->exchange_rate,
        ]);

        return $currency->fresh();
    }
}
