<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Create Currency Action.
 *
 * Handles creation of new currencies with automatic default currency management.
 *
 * @package Modules\Currency\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CreateCurrencyAction extends Action
{
    /**
     * Create a new CreateCurrencyAction instance.
     *
     * @param CurrencyRepository $repository The currency repository
     */
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    /**
     * Execute the currency creation action.
     *
     * @param CurrencyData $data The validated currency data
     * 
     * @return Currency The newly created currency
     * 
     * @throws \Exception When creation fails
     */
    public function execute(CurrencyData $data): Currency
    {
        if ($data->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        return $this->repository->create([
            'code' => strtoupper($data->code),
            'name' => $data->name,
            'symbol' => $data->symbol,
            'decimal_places' => $data->decimal_places,
            'is_active' => $data->is_active,
            'is_default' => $data->is_default,
            'exchange_rate' => $data->exchange_rate,
        ]);
    }
}
