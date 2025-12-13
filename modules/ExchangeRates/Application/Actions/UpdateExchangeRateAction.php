<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\DTO\ExchangeRateData;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

final class UpdateExchangeRateAction extends Action
{
    public function __construct(
        private readonly ExchangeRateRepository $repository
    ) {}

    public function execute(ExchangeRateData $data): ExchangeRate
    {
        $inverseRate = $data->inverse_rate ?? (1 / $data->rate);

        return $this->repository->updateOrCreateRate(
            $data->base_currency_id,
            $data->target_currency_id,
            [
                'rate' => $data->rate,
                'inverse_rate' => $inverseRate,
                'provider' => $data->provider,
                'is_frozen' => $data->is_frozen,
                'valid_from' => $data->valid_from,
                'valid_until' => $data->valid_until,
            ]
        );
    }
}
