<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\ExchangeRates\Domain\Models\ExchangeRateHistory;

/**
 * Import History Action.
 *
 * Imports exchange rate history from external data.
 *
 * @package Modules\ExchangeRates\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ImportHistoryAction extends Action
{
    /**
     * Execute the import action.
     *
     * @param array<int, array<string, mixed>> $data The history data to import
     *
     * @return array{imported: int} Import result
     */
    public function execute(array $data): array
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
