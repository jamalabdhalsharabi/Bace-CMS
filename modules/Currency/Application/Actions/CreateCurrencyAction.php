<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

final class CreateCurrencyAction extends Action
{
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

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
