<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Currency\Domain\Models\Currency;
use Modules\ExchangeRates\Contracts\ExchangeRateServiceContract;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;
use Modules\ExchangeRates\Domain\Models\RateAlert;

class ExchangeRateService implements ExchangeRateServiceContract
{
    public function fetchFromApi(?string $provider = null): array
    {
        $provider = $provider ?? config('exchange-rates.default_provider');
        $config = config("exchange-rates.providers.{$provider}");
        
        try {
            $baseCurrency = Currency::where('is_default', true)->first();
            if (!$baseCurrency) {
                return ['success' => false, 'error' => 'No default currency'];
            }

            $response = Http::get($config['url'] . $baseCurrency->code);
            
            if (!$response->successful()) {
                return ['success' => false, 'error' => 'API request failed'];
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];
            $updated = 0;

            DB::transaction(function () use ($baseCurrency, $rates, $provider, &$updated) {
                $currencies = Currency::where('is_active', true)->get()->keyBy('code');
                
                foreach ($rates as $code => $rate) {
                    if (!isset($currencies[$code])) continue;
                    
                    $targetCurrency = $currencies[$code];
                    
                    $exchangeRate = ExchangeRate::updateOrCreate(
                        ['base_currency_id' => $baseCurrency->id, 'target_currency_id' => $targetCurrency->id],
                        [
                            'rate' => $rate,
                            'inverse_rate' => 1 / $rate,
                            'provider' => $provider,
                            'valid_from' => now(),
                        ]
                    );

                    if (!$exchangeRate->is_frozen) {
                        ExchangeRateHistory::create([
                            'base_currency_id' => $baseCurrency->id,
                            'target_currency_id' => $targetCurrency->id,
                            'rate' => $rate,
                            'provider' => $provider,
                            'recorded_at' => now(),
                        ]);
                        $updated++;
                    }
                }
            });

            Cache::forget('exchange_rates');
            $this->checkAlerts();

            return ['success' => true, 'updated' => $updated];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function scheduleUpdate(string $frequency = 'hourly'): bool
    {
        // This would typically update a scheduled task configuration
        return true;
    }

    public function getRate(string $baseId, string $targetId): ?ExchangeRate
    {
        return ExchangeRate::forPair($baseId, $targetId)->active()->first();
    }

    public function getAllRates(): Collection
    {
        return Cache::remember('exchange_rates', config('exchange-rates.cache_duration', 3600), function () {
            return ExchangeRate::with(['baseCurrency', 'targetCurrency'])->active()->get();
        });
    }

    public function updateManually(string $baseId, string $targetId, float $rate): ExchangeRate
    {
        return DB::transaction(function () use ($baseId, $targetId, $rate) {
            $exchangeRate = ExchangeRate::updateOrCreate(
                ['base_currency_id' => $baseId, 'target_currency_id' => $targetId],
                [
                    'rate' => $rate,
                    'inverse_rate' => 1 / $rate,
                    'provider' => 'manual',
                    'valid_from' => now(),
                ]
            );

            ExchangeRateHistory::create([
                'base_currency_id' => $baseId,
                'target_currency_id' => $targetId,
                'rate' => $rate,
                'provider' => 'manual',
                'recorded_at' => now(),
            ]);

            Cache::forget('exchange_rates');
            return $exchangeRate;
        });
    }

    public function freeze(ExchangeRate $rate): ExchangeRate
    {
        $rate->freeze();
        Cache::forget('exchange_rates');
        return $rate->fresh();
    }

    public function unfreeze(ExchangeRate $rate): ExchangeRate
    {
        $rate->unfreeze();
        Cache::forget('exchange_rates');
        return $rate->fresh();
    }

    public function getHistory(string $baseId, string $targetId, ?string $from = null, ?string $to = null): Collection
    {
        $query = ExchangeRateHistory::where('base_currency_id', $baseId)
            ->where('target_currency_id', $targetId);
        
        if ($from) $query->where('recorded_at', '>=', $from);
        if ($to) $query->where('recorded_at', '<=', $to);
        
        return $query->orderBy('recorded_at', 'desc')->get();
    }

    public function cleanOldHistory(int $days = 365): int
    {
        return ExchangeRateHistory::where('recorded_at', '<', now()->subDays($days))->delete();
    }

    public function importHistory(array $data): array
    {
        $imported = 0;
        $errors = [];

        foreach ($data as $record) {
            try {
                ExchangeRateHistory::create($record);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    public function exportHistory(string $baseId, string $targetId, string $format = 'json'): array
    {
        $history = $this->getHistory($baseId, $targetId);
        return $history->map(fn($h) => [
            'rate' => $h->rate,
            'provider' => $h->provider,
            'recorded_at' => $h->recorded_at->toISOString(),
        ])->toArray();
    }

    public function createAlert(array $data): RateAlert
    {
        return RateAlert::create([
            'user_id' => auth()->id(),
            'base_currency_id' => $data['base_currency_id'],
            'target_currency_id' => $data['target_currency_id'],
            'condition' => $data['condition'],
            'threshold' => $data['threshold'],
            'is_active' => true,
        ]);
    }

    public function deactivateAlert(RateAlert $alert): RateAlert
    {
        $alert->deactivate();
        return $alert->fresh();
    }

    public function checkAlerts(): array
    {
        $triggered = [];
        $alerts = RateAlert::where('is_active', true)->get();

        foreach ($alerts as $alert) {
            $rate = $this->getRate($alert->base_currency_id, $alert->target_currency_id);
            if ($rate && $alert->checkTrigger($rate->rate)) {
                $alert->trigger();
                $triggered[] = $alert;
            }
        }

        return $triggered;
    }

    public function convert(float $amount, string $fromCurrencyId, string $toCurrencyId): float
    {
        if ($fromCurrencyId === $toCurrencyId) return $amount;
        
        $rate = $this->getRate($fromCurrencyId, $toCurrencyId);
        if ($rate) return $rate->convert($amount);
        
        $inverseRate = $this->getRate($toCurrencyId, $fromCurrencyId);
        if ($inverseRate) return $inverseRate->inverseConvert($amount);
        
        return $amount;
    }

    public function detectConflicts(): array
    {
        $conflicts = [];
        $rates = ExchangeRate::active()->get();

        foreach ($rates as $rate) {
            $inverse = ExchangeRate::forPair($rate->target_currency_id, $rate->base_currency_id)->first();
            if ($inverse) {
                $expectedInverse = 1 / $rate->rate;
                $diff = abs($inverse->rate - $expectedInverse);
                if ($diff > 0.001) {
                    $conflicts[] = [
                        'pair' => "{$rate->baseCurrency->code}/{$rate->targetCurrency->code}",
                        'rate' => $rate->rate,
                        'inverse_rate' => $inverse->rate,
                        'expected_inverse' => $expectedInverse,
                        'difference' => $diff,
                    ];
                }
            }
        }

        return $conflicts;
    }

    public function updateProductPrices(string $currencyId): int
    {
        // This would update product prices based on new exchange rates
        // Implementation depends on Products module structure
        return 0;
    }
}
