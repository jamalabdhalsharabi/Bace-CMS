<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\DTO\RateAlertData;
use Modules\ExchangeRates\Domain\Models\RateAlert;

final class CreateRateAlertAction extends Action
{
    public function execute(RateAlertData $data): RateAlert
    {
        return RateAlert::create([
            'user_id' => $data->user_id ?? $this->userId(),
            'base_currency_id' => $data->base_currency_id,
            'target_currency_id' => $data->target_currency_id,
            'condition' => $data->condition,
            'threshold' => $data->threshold,
            'is_active' => true,
        ]);
    }
}
