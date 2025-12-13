<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;

class ExchangeRateRepository extends BaseRepository
{
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    public function getRate(string $baseId, string $targetId): ?ExchangeRate
    {
        return $this->model->forPair($baseId, $targetId)->active()->first();
    }

    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->active()->with(['baseCurrency', 'targetCurrency'])->get();
    }

    public function getFrozen(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->frozen()->with(['baseCurrency', 'targetCurrency'])->get();
    }

    public function updateOrCreateRate(string $baseId, string $targetId, array $data): ExchangeRate
    {
        return $this->model->updateOrCreate(
            ['base_currency_id' => $baseId, 'target_currency_id' => $targetId],
            $data
        );
    }
}
