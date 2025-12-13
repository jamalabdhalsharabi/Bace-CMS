<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\RateAlert;

final class DeactivateRateAlertAction extends Action
{
    public function execute(RateAlert $alert): RateAlert
    {
        $alert->update(['is_active' => false]);

        return $alert->fresh();
    }
}
