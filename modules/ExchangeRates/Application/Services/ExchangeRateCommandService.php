<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Services;

use Modules\ExchangeRates\Application\Actions\ConvertCurrencyAction;
use Modules\ExchangeRates\Application\Actions\CreateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\DeactivateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\FetchExchangeRatesAction;
use Modules\ExchangeRates\Application\Actions\FreezeExchangeRateAction;
use Modules\ExchangeRates\Application\Actions\UpdateExchangeRateAction;
use Modules\ExchangeRates\Domain\DTO\ExchangeRateData;
use Modules\ExchangeRates\Domain\DTO\RateAlertData;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;
use Modules\ExchangeRates\Domain\Models\RateAlert;

final class ExchangeRateCommandService
{
    public function __construct(
        private readonly UpdateExchangeRateAction $updateAction,
        private readonly FreezeExchangeRateAction $freezeAction,
        private readonly FetchExchangeRatesAction $fetchAction,
        private readonly CreateRateAlertAction $createAlertAction,
        private readonly DeactivateRateAlertAction $deactivateAlertAction,
        private readonly ConvertCurrencyAction $convertAction,
    ) {}

    public function updateRate(ExchangeRateData $data): ExchangeRate
    {
        return $this->updateAction->execute($data);
    }

    public function freeze(ExchangeRate $rate): ExchangeRate
    {
        return $this->freezeAction->execute($rate);
    }

    public function unfreeze(ExchangeRate $rate): ExchangeRate
    {
        return $this->freezeAction->unfreeze($rate);
    }

    public function fetchFromApi(?string $provider = null): array
    {
        return $this->fetchAction->execute($provider);
    }

    public function createAlert(RateAlertData $data): RateAlert
    {
        return $this->createAlertAction->execute($data);
    }

    public function deactivateAlert(RateAlert $alert): RateAlert
    {
        return $this->deactivateAlertAction->execute($alert);
    }

    public function convert(float $amount, string $fromCurrencyId, string $toCurrencyId): ?float
    {
        return $this->convertAction->execute($amount, $fromCurrencyId, $toCurrencyId);
    }

    public function cleanOldHistory(int $days = 365): int
    {
        return ExchangeRateHistory::where('recorded_at', '<', now()->subDays($days))->delete();
    }

    public function importHistory(array $data): array
    {
        $imported = 0;

        foreach ($data as $item) {
            ExchangeRateHistory::create([
                'base_currency_id' => $item['base_currency_id'],
                'target_currency_id' => $item['target_currency_id'],
                'rate' => $item['rate'],
                'provider' => $item['provider'] ?? 'import',
                'recorded_at' => $item['recorded_at'] ?? now(),
            ]);
            $imported++;
        }

        return ['imported' => $imported];
    }
}
