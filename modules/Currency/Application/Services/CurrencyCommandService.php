<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Currency\Application\Actions\CreateCurrencyAction;
use Modules\Currency\Application\Actions\DeleteCurrencyAction;
use Modules\Currency\Application\Actions\UpdateCurrencyAction;
use Modules\Currency\Application\Actions\UpdateExchangeRateAction;
use Modules\Currency\Domain\DTO\CurrencyData;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Currency Command Service.
 */
final class CurrencyCommandService
{
    public function __construct(
        private readonly CreateCurrencyAction $createAction,
        private readonly UpdateCurrencyAction $updateAction,
        private readonly DeleteCurrencyAction $deleteAction,
        private readonly UpdateExchangeRateAction $updateExchangeRateAction,
        private readonly CurrencyRepository $repository,
    ) {}

    public function create(CurrencyData $data): Currency
    {
        return $this->createAction->execute($data);
    }

    public function update(Currency $currency, CurrencyData $data): Currency
    {
        return $this->updateAction->execute($currency, $data);
    }

    public function delete(Currency $currency): bool
    {
        return $this->deleteAction->execute($currency);
    }

    public function setDefault(Currency $currency): Currency
    {
        Currency::where('is_default', true)->update(['is_default' => false]);
        $this->repository->update($currency->id, ['is_default' => true]);

        return $currency->fresh();
    }

    public function updateExchangeRate(Currency $currency, float $rate): Currency
    {
        return $this->updateExchangeRateAction->execute($currency, $rate);
    }
}
