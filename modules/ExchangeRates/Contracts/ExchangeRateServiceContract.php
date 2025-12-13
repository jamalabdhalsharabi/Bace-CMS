<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\RateAlert;

/**
 * Interface ExchangeRateServiceContract
 * 
 * Defines the contract for exchange rate management services.
 * Handles rate fetching, caching, history, alerts, and currency conversion.
 * 
 * @package Modules\ExchangeRates\Contracts
 */
interface ExchangeRateServiceContract
{
    /**
     * Fetch exchange rates from an external API provider.
     *
     * @param string|null $provider The provider name (null for default)
     * @return array Result with 'success' status and 'updated' count or 'error'
     */
    public function fetchFromApi(?string $provider = null): array;

    /**
     * Schedule automatic rate updates.
     *
     * @param string $frequency Update frequency (hourly, daily, etc.)
     * @return bool Success status
     */
    public function scheduleUpdate(string $frequency = 'hourly'): bool;
    
    /**
     * Get exchange rate for a specific currency pair.
     *
     * @param string $baseId Base currency ID
     * @param string $targetId Target currency ID
     * @return ExchangeRate|null The exchange rate or null if not found
     */
    public function getRate(string $baseId, string $targetId): ?ExchangeRate;

    /**
     * Get all active exchange rates.
     *
     * @return Collection Collection of ExchangeRate models
     */
    public function getAllRates(): Collection;

    /**
     * Manually update or create an exchange rate.
     *
     * @param string $baseId Base currency ID
     * @param string $targetId Target currency ID
     * @param float $rate The exchange rate value
     * @return ExchangeRate The updated or created exchange rate
     */
    public function updateManually(string $baseId, string $targetId, float $rate): ExchangeRate;
    
    /**
     * Freeze an exchange rate to prevent automatic updates.
     *
     * @param ExchangeRate $rate The rate to freeze
     * @return ExchangeRate The frozen rate
     */
    public function freeze(ExchangeRate $rate): ExchangeRate;

    /**
     * Unfreeze an exchange rate to allow automatic updates.
     *
     * @param ExchangeRate $rate The rate to unfreeze
     * @return ExchangeRate The unfrozen rate
     */
    public function unfreeze(ExchangeRate $rate): ExchangeRate;
    
    /**
     * Get historical exchange rates for a currency pair.
     *
     * @param string $baseId Base currency ID
     * @param string $targetId Target currency ID
     * @param string|null $from Start date filter
     * @param string|null $to End date filter
     * @return Collection Collection of historical rates
     */
    public function getHistory(string $baseId, string $targetId, ?string $from = null, ?string $to = null): Collection;

    /**
     * Clean old historical rate records.
     *
     * @param int $days Delete records older than this many days
     * @return int Number of deleted records
     */
    public function cleanOldHistory(int $days = 365): int;

    /**
     * Import historical exchange rate data.
     *
     * @param array $data Array of historical rate records
     * @return array Import results with 'imported' count and 'errors'
     */
    public function importHistory(array $data): array;

    /**
     * Export historical exchange rate data.
     *
     * @param string $baseId Base currency ID
     * @param string $targetId Target currency ID
     * @param string $format Export format (json, csv)
     * @return array Exported data
     */
    public function exportHistory(string $baseId, string $targetId, string $format = 'json'): array;
    
    /**
     * Create a new rate alert.
     *
     * @param array $data Alert data (base_currency_id, target_currency_id, condition, threshold)
     * @return RateAlert The created alert
     */
    public function createAlert(array $data): RateAlert;

    /**
     * Deactivate a rate alert.
     *
     * @param RateAlert $alert The alert to deactivate
     * @return RateAlert The deactivated alert
     */
    public function deactivateAlert(RateAlert $alert): RateAlert;

    /**
     * Check all active alerts and trigger notifications.
     *
     * @return array Array of triggered alerts
     */
    public function checkAlerts(): array;
    
    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount The amount to convert
     * @param string $fromCurrencyId Source currency ID
     * @param string $toCurrencyId Target currency ID
     * @return float The converted amount
     */
    public function convert(float $amount, string $fromCurrencyId, string $toCurrencyId): float;
    
    /**
     * Detect conflicting exchange rates (inconsistent inverse rates).
     *
     * @return array Array of detected conflicts
     */
    public function detectConflicts(): array;
    
    /**
     * Update product prices based on new exchange rates.
     *
     * @param string $currencyId The currency ID to update prices for
     * @return int Number of updated products
     */
    public function updateProductPrices(string $currencyId): int;
}
