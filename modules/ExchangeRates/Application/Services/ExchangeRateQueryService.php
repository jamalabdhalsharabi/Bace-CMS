<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;
use Modules\ExchangeRates\Domain\Models\RateAlert;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

final class ExchangeRateQueryService
{
    public function __construct(
        private readonly ExchangeRateRepository $repository
    ) {}

    public function getAllRates(): Collection
    {
        return $this->repository->getAllActive();
    }

    public function getRate(string $baseId, string $targetId): ?ExchangeRate
    {
        return $this->repository->getRate($baseId, $targetId);
    }

    public function getFrozenRates(): Collection
    {
        return $this->repository->getFrozen();
    }

    public function getHistory(string $baseId, string $targetId, ?string $from = null, ?string $to = null): Collection
    {
        $query = ExchangeRateHistory::where('base_currency_id', $baseId)
            ->where('target_currency_id', $targetId)
            ->orderBy('recorded_at', 'desc');

        if ($from) {
            $query->where('recorded_at', '>=', $from);
        }

        if ($to) {
            $query->where('recorded_at', '<=', $to);
        }

        return $query->get();
    }

    public function getUserAlerts(?string $userId = null): Collection
    {
        return RateAlert::where('user_id', $userId ?? auth()->id())
            ->where('is_active', true)
            ->with(['baseCurrency', 'targetCurrency'])
            ->get();
    }

    public function detectConflicts(): array
    {
        $rates = $this->repository->getAllActive();
        $conflicts = [];

        foreach ($rates as $rate) {
            $inverse = $this->repository->getRate($rate->target_currency_id, $rate->base_currency_id);

            if ($inverse) {
                $expectedInverse = 1 / $rate->rate;
                $difference = abs($inverse->rate - $expectedInverse);

                if ($difference > 0.0001) {
                    $conflicts[] = [
                        'pair' => "{$rate->baseCurrency->code}/{$rate->targetCurrency->code}",
                        'rate' => $rate->rate,
                        'inverse_rate' => $inverse->rate,
                        'expected_inverse' => $expectedInverse,
                        'difference' => $difference,
                    ];
                }
            }
        }

        return $conflicts;
    }

    public function exportHistory(string $baseId, string $targetId): array
    {
        return ExchangeRateHistory::where('base_currency_id', $baseId)
            ->where('target_currency_id', $targetId)
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->map(fn ($h) => [
                'date' => $h->recorded_at->toDateString(),
                'rate' => $h->rate,
                'provider' => $h->provider,
            ])
            ->toArray();
    }
}
