<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepository;

/**
 * Delete Currency Action.
 *
 * Handles deletion of currencies with protection for default currency.
 *
 * @package Modules\Currency\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeleteCurrencyAction extends Action
{
    /**
     * Create a new DeleteCurrencyAction instance.
     *
     * @param CurrencyRepository $repository The currency repository
     */
    public function __construct(
        private readonly CurrencyRepository $repository
    ) {}

    /**
     * Execute the currency deletion action.
     *
     * @param Currency $currency The currency instance to delete
     * 
     * @return bool True if deletion was successful, false if currency is default
     * 
     * @throws \Exception When deletion fails
     */
    public function execute(Currency $currency): bool
    {
        if ($currency->is_default) {
            return false;
        }

        return $this->repository->delete($currency->id);
    }
}
