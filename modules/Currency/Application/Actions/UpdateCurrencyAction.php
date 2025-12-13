<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

final class UpdateCurrencyAction extends Action
{
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

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
