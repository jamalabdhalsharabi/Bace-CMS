<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Currency\Domain\Models\Currency;

/**
 * Currency Repository.
 *
 * Read-only repository for Currency model queries.
 * All write operations must be performed through Action classes.
 *
 * @extends BaseRepository<Currency>
 *
 * @package Modules\Currency\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CurrencyRepository extends BaseRepository
{
    /**
     * Create a new CurrencyRepository instance.
     *
     * @param Currency $model The Currency model instance
     */
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
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
