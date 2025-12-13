<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

final class UpdateExchangeRateAction extends Action
{
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    public function execute(Currency $currency, float $rate): Currency
    {
        $this->repository->update($currency->id, ['exchange_rate' => $rate]);

        return $currency->fresh();
    }
}
