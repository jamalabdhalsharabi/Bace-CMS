<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

final class ConvertCurrencyAction extends Action
{
    public function __construct(
        private readonly ExchangeRateRepository $repository
    ) {}

    public function execute(float $amount, string $fromCurrencyId, string $toCurrencyId): ?float
    {
        if ($fromCurrencyId === $toCurrencyId) {
            return $amount;
        }

        $rate = $this->repository->getRate($fromCurrencyId, $toCurrencyId);

        if ($rate) {
            return round($amount * $rate->rate, 2);
        }

        $inverseRate = $this->repository->getRate($toCurrencyId, $fromCurrencyId);

        if ($inverseRate) {
            return round($amount * $inverseRate->inverse_rate, 2);
        }

        return null;
    }
}
