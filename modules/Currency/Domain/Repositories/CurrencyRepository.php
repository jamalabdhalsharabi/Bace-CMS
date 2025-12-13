<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Currency\Domain\Models\Currency;

/**
 * Currency Repository.
 *
 * @extends BaseRepository<Currency>
 */
final class CurrencyRepository extends BaseRepository
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getDefault(): ?Currency
    {
        return $this->query()->where('is_default', true)->first();
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->query()->where('code', strtoupper($code))->first();
    }
}
