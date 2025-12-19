<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

/**
 * Exchange Rate Repository Interface.
 *
 * Read-only interface for ExchangeRate queries.
 * All write operations should be performed through Action classes.
 *
 * @extends RepositoryInterface<ExchangeRate>
 *
 * @package Modules\ExchangeRates\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface ExchangeRateRepositoryInterface extends RepositoryInterface
{
    /**
     * Get exchange rate for a currency pair.
     *
     * @param string $baseId The base currency ID
     * @param string $targetId The target currency ID
     *
     * @return ExchangeRate|null
     */
    public function getRate(string $baseId, string $targetId): ?ExchangeRate;

    /**
     * Get all active exchange rates.
     *
     * @return Collection<int, ExchangeRate>
     */
    public function getAllActive(): Collection;

    /**
     * Get frozen exchange rates.
     *
     * @return Collection<int, ExchangeRate>
     */
    public function getFrozen(): Collection;

    /**
     * Find exchange rate by ID with currencies.
     *
     * @param string $id The exchange rate ID
     *
     * @return ExchangeRate|null
     */
    public function findById(string $id): ?ExchangeRate;
}
