<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\ExchangeRates\Domain\Contracts\ExchangeRateRepositoryInterface;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

/**
 * Exchange Rate Repository Implementation.
 *
 * Read-only repository for ExchangeRate model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<ExchangeRate>
 * @implements ExchangeRateRepositoryInterface
 *
 * @package Modules\ExchangeRates\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ExchangeRateRepository extends BaseRepository implements ExchangeRateRepositoryInterface
{
    /**
     * Create a new ExchangeRateRepository instance.
     *
     * @param ExchangeRate $model The ExchangeRate model instance
     */
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    /**
     * Get exchange rate for a currency pair.
     *
     * Uses model scopes for efficient querying.
     *
     * @param string $baseId The base currency ID
     * @param string $targetId The target currency ID
     *
     * @return ExchangeRate|null
     */
    public function getRate(string $baseId, string $targetId): ?ExchangeRate
    {
        return $this->query()
            ->forPair($baseId, $targetId)
            ->active()
            ->first();
    }

    /**
     * Get all active exchange rates with eager-loaded currencies.
     *
     * @return Collection<int, ExchangeRate>
     */
    public function getAllActive(): Collection
    {
        return $this->query()
            ->active()
            ->with(['baseCurrency', 'targetCurrency'])
            ->get();
    }

    /**
     * Get all frozen exchange rates.
     *
     * @return Collection<int, ExchangeRate>
     */
    public function getFrozen(): Collection
    {
        return $this->query()
            ->frozen()
            ->with(['baseCurrency', 'targetCurrency'])
            ->get();
    }

    /**
     * Find exchange rate by ID with currencies.
     *
     * @param string $id The exchange rate ID
     *
     * @return ExchangeRate|null
     */
    public function findById(string $id): ?ExchangeRate
    {
        return $this->query()
            ->with(['baseCurrency', 'targetCurrency'])
            ->find($id);
    }
}
