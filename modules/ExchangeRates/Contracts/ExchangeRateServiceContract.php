<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\RateAlert;

interface ExchangeRateServiceContract
{
    // Fetch & Sync
    public function fetchFromApi(?string $provider = null): array;
    public function scheduleUpdate(string $frequency = 'hourly'): bool;
    
    // CRUD
    public function getRate(string $baseId, string $targetId): ?ExchangeRate;
    public function getAllRates(): Collection;
    public function updateManually(string $baseId, string $targetId, float $rate): ExchangeRate;
    
    // Freeze
    public function freeze(ExchangeRate $rate): ExchangeRate;
    public function unfreeze(ExchangeRate $rate): ExchangeRate;
    
    // History
    public function getHistory(string $baseId, string $targetId, ?string $from = null, ?string $to = null): Collection;
    public function cleanOldHistory(int $days = 365): int;
    public function importHistory(array $data): array;
    public function exportHistory(string $baseId, string $targetId, string $format = 'json'): array;
    
    // Alerts
    public function createAlert(array $data): RateAlert;
    public function deactivateAlert(RateAlert $alert): RateAlert;
    public function checkAlerts(): array;
    
    // Conversion
    public function convert(float $amount, string $fromCurrencyId, string $toCurrencyId): float;
    
    // Conflict Detection
    public function detectConflicts(): array;
    
    // Impact
    public function updateProductPrices(string $currencyId): int;
}
