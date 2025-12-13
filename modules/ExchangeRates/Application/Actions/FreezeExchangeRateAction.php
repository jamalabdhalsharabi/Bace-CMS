<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

final class FreezeExchangeRateAction extends Action
{
    public function __construct(
        private readonly ExchangeRateRepository $repository
    ) {}

    public function execute(ExchangeRate $rate): ExchangeRate
    {
        $this->repository->update($rate->id, [
            'is_frozen' => true,
            'frozen_at' => now(),
            'frozen_by' => $this->userId(),
        ]);

        return $rate->fresh();
    }

    public function unfreeze(ExchangeRate $rate): ExchangeRate
    {
        $this->repository->update($rate->id, [
            'is_frozen' => false,
            'frozen_at' => null,
            'frozen_by' => null,
        ]);

        return $rate->fresh();
    }
}
