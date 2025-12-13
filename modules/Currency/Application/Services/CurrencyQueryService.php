<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Currency Query Service.
 */
final class CurrencyQueryService
{
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?Currency
    {
        return $this->repository->find($id);
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->repository->findByCode($code);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getDefault(): ?Currency
    {
        return $this->repository->getDefault();
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $fromCurrency = $this->findByCode($from);
        $toCurrency = $this->findByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            return $amount;
        }

        $baseAmount = $amount / ($fromCurrency->exchange_rate ?: 1);

        return round($baseAmount * ($toCurrency->exchange_rate ?: 1), $toCurrency->decimal_places);
    }
}
