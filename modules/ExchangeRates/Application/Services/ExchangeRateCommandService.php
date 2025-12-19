<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Services;

use Modules\ExchangeRates\Application\Actions\CleanOldHistoryAction;
use Modules\ExchangeRates\Application\Actions\ConvertCurrencyAction;
use Modules\ExchangeRates\Application\Actions\CreateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\DeactivateRateAlertAction;
use Modules\ExchangeRates\Application\Actions\FetchExchangeRatesAction;
use Modules\ExchangeRates\Application\Actions\FreezeExchangeRateAction;
use Modules\ExchangeRates\Application\Actions\ImportHistoryAction;
use Modules\ExchangeRates\Application\Actions\UpdateExchangeRateAction;
use Modules\ExchangeRates\Domain\DTO\ExchangeRateData;
use Modules\ExchangeRates\Domain\DTO\RateAlertData;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\RateAlert;

/**
 * Exchange Rate Command Service.
 *
 * Orchestrates all exchange rate write operations via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\ExchangeRates\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ExchangeRateCommandService
{
    /**
     * Create a new ExchangeRateCommandService instance.
     *
     * @param UpdateExchangeRateAction $updateAction Action for updating rates
     * @param FreezeExchangeRateAction $freezeAction Action for freezing rates
     * @param FetchExchangeRatesAction $fetchAction Action for fetching from API
     * @param CreateRateAlertAction $createAlertAction Action for creating alerts
     * @param DeactivateRateAlertAction $deactivateAlertAction Action for deactivating alerts
     * @param ConvertCurrencyAction $convertAction Action for currency conversion
     * @param CleanOldHistoryAction $cleanAction Action for cleaning old history
     * @param ImportHistoryAction $importAction Action for importing history
     */
    public function __construct(
        private readonly UpdateExchangeRateAction $updateAction,
        private readonly FreezeExchangeRateAction $freezeAction,
        private readonly FetchExchangeRatesAction $fetchAction,
        private readonly CreateRateAlertAction $createAlertAction,
        private readonly DeactivateRateAlertAction $deactivateAlertAction,
        private readonly ConvertCurrencyAction $convertAction,
        private readonly CleanOldHistoryAction $cleanAction,
        private readonly ImportHistoryAction $importAction,
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

    /**
     * Clean old history records.
     *
     * @param int $days Number of days to keep records
     *
     * @return int Number of deleted records
     */
    public function cleanOldHistory(int $days = 365): int
    {
        return $this->cleanAction->execute($days);
    }

    /**
     * Import history from external data.
     *
     * @param array<int, array<string, mixed>> $data The history data
     *
     * @return array{imported: int} Import result
     */
    public function importHistory(array $data): array
    {
        return $this->importAction->execute($data);
    }
}
