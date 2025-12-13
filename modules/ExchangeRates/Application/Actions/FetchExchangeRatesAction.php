<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Illuminate\Support\Facades\Http;
use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\Models\Currency;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;
use Modules\ExchangeRates\Domain\Repositories\ExchangeRateRepository;

final class FetchExchangeRatesAction extends Action
{
    public function __construct(
        private readonly ExchangeRateRepository $repository
    ) {}

    public function execute(?string $provider = null): array
    {
        $provider = $provider ?? config('exchange-rates.default_provider', 'exchangerate-api');

        try {
            $baseCurrency = Currency::where('is_default', true)->first();
            if (!$baseCurrency) {
                return ['success' => false, 'error' => 'No default currency found'];
            }

            $rates = $this->fetchFromProvider($provider, $baseCurrency->code);
            if (!$rates) {
                return ['success' => false, 'error' => 'Failed to fetch rates'];
            }

            $updated = $this->updateRates($baseCurrency, $rates, $provider);

            return ['success' => true, 'updated' => $updated];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function fetchFromProvider(string $provider, string $baseCode): ?array
    {
        $config = config("exchange-rates.providers.{$provider}");
        if (!$config) return null;

        $url = str_replace(
            ['{api_key}', '{base}'],
            [$config['api_key'] ?? '', $baseCode],
            $config['url']
        );

        $response = Http::get($url);
        if (!$response->successful()) return null;

        $data = $response->json();
        return $data['rates'] ?? $data['conversion_rates'] ?? null;
    }

    private function updateRates(Currency $baseCurrency, array $rates, string $provider): int
    {
        $updated = 0;
        $currencies = Currency::where('is_active', true)->get()->keyBy('code');

        foreach ($rates as $code => $rate) {
            if (!isset($currencies[$code])) continue;

            $targetCurrency = $currencies[$code];

            $existingRate = ExchangeRate::forPair($baseCurrency->id, $targetCurrency->id)->first();
            if ($existingRate && $existingRate->is_frozen) continue;

            ExchangeRateHistory::create([
                'base_currency_id' => $baseCurrency->id,
                'target_currency_id' => $targetCurrency->id,
                'rate' => $rate,
                'provider' => $provider,
                'recorded_at' => now(),
            ]);

            $this->repository->updateOrCreateRate($baseCurrency->id, $targetCurrency->id, [
                'rate' => $rate,
                'inverse_rate' => 1 / $rate,
                'provider' => $provider,
            ]);

            $updated++;
        }

        return $updated;
    }
}
