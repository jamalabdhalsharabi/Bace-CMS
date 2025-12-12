<?php

declare(strict_types=1);

namespace Modules\Currency\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Currency\Domain\Models\Currency;

interface CurrencyServiceContract
{
    public function all(): Collection;

    public function getActive(): Collection;

    public function find(string $id): ?Currency;

    public function findByCode(string $code): ?Currency;

    public function getDefault(): ?Currency;

    public function create(array $data): Currency;

    public function update(Currency $currency, array $data): Currency;

    public function delete(Currency $currency): bool;

    public function convert(float $amount, string $from, string $to): float;

    public function format(float $amount, ?string $currencyCode = null): string;
}
