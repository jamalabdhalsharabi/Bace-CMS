<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Currency\Application\Services\CurrencyCommandService;
use Modules\Currency\Application\Services\CurrencyQueryService;
use Modules\Currency\Http\Requests\ConvertCurrencyRequest;
use Modules\Currency\Http\Requests\CreateCurrencyRequest;
use Modules\Currency\Http\Requests\UpdateCurrencyRequest;
use Modules\Currency\Http\Requests\UpdateRateRequest;
use Modules\Currency\Http\Requests\FormatAmountRequest;
use Modules\Currency\Http\Resources\CurrencyResource;

class CurrencyController extends BaseController
{
    public function __construct(
        protected CurrencyQueryService $queryService,
        protected CurrencyCommandService $commandService
    ) {
    }

    /**
     * Display a listing of active currencies.
     *
     * @return JsonResponse Collection of active currencies
     */
    public function index(): JsonResponse
    {
        try {
            $currencies = $this->queryService->getActive();
            return $this->success(CurrencyResource::collection($currencies));
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve currencies: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified currency by its UUID.
     *
     * @param string $id The UUID of the currency
     * @return JsonResponse The currency data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);

            if (!$currency) {
                return $this->notFound('Currency not found');
            }

            return $this->success(new CurrencyResource($currency));
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve currency: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created currency.
     *
     * @param CreateCurrencyRequest $request The validated request
     * @return JsonResponse The created currency (HTTP 201)
     */
    public function store(CreateCurrencyRequest $request): JsonResponse
    {
        try {
            $currency = $this->commandService->create($request->validated());
            return $this->created(new CurrencyResource($currency), 'Currency created successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to create currency: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified currency.
     *
     * @param UpdateCurrencyRequest $request The validated request
     * @param string $id The UUID of the currency
     * @return JsonResponse The updated currency or 404 error
     */
    public function update(UpdateCurrencyRequest $request, string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);

            if (!$currency) {
                return $this->notFound('Currency not found');
            }

            $currency = $this->commandService->update($currency, $request->validated());

            return $this->success(new CurrencyResource($currency), 'Currency updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update currency: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified currency.
     *
     * @param string $id The UUID of the currency
     * @return JsonResponse Success message or error
     * @throws \RuntimeException If currency cannot be deleted
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);

            if (!$currency) {
                return $this->notFound('Currency not found');
            }

            $this->commandService->delete($currency);

            return $this->success(null, 'Currency deleted successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Failed to delete currency: ' . $e->getMessage());
        }
    }

    /** Convert an amount from one currency to another. */
    public function convert(ConvertCurrencyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $converted = $this->queryService->convert(
                (float) $validated['amount'],
                $validated['from'],
                $validated['to']
            );
            return $this->success([
                'amount' => $validated['amount'],
                'from' => $validated['from'],
                'to' => $validated['to'],
                'converted' => $converted,
                'formatted' => $this->queryService->format($converted, $validated['to']),
            ]);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /** Get all currencies including inactive. */
    public function all(): JsonResponse
    {
        try {
            $currencies = $this->queryService->getAll();
            return $this->success(CurrencyResource::collection($currencies));
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve currencies: ' . $e->getMessage());
        }
    }

    /** Activate currency. */
    public function activate(string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);
            if (!$currency) return $this->notFound('Currency not found');
            $currency = $this->commandService->activate($id);
            return $this->success(new CurrencyResource($currency), 'Currency activated');
        } catch (\Exception $e) {
            return $this->error('Failed to activate currency: ' . $e->getMessage());
        }
    }

    /** Deactivate currency. */
    public function deactivate(string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);
            if (!$currency) return $this->notFound('Currency not found');
            $currency = $this->commandService->deactivate($id);
            return $this->success(new CurrencyResource($currency), 'Currency deactivated');
        } catch (\Exception $e) {
            return $this->error('Failed to deactivate currency: ' . $e->getMessage());
        }
    }

    /** Set default currency. */
    public function setDefault(string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);
            if (!$currency) return $this->notFound('Currency not found');
            $currency = $this->commandService->setDefault($id);
            return $this->success(new CurrencyResource($currency), 'Default currency set');
        } catch (\Exception $e) {
            return $this->error('Failed to set default currency: ' . $e->getMessage());
        }
    }

    /** Update exchange rate. */
    public function updateRate(UpdateRateRequest $request, string $id): JsonResponse
    {
        try {
            $currency = $this->queryService->find($id);
            if (!$currency) return $this->notFound('Currency not found');
            $this->commandService->updateRate($id, $request->validated()['rate']);
            return $this->success(null, 'Exchange rate updated');
        } catch (\Exception $e) {
            return $this->error('Failed to update exchange rate: ' . $e->getMessage());
        }
    }

    /** Sync rates from external API. */
    public function syncRates(): JsonResponse
    {
        try {
            $result = $this->commandService->syncRatesFromApi();
            return $this->success($result, 'Exchange rates synced');
        } catch (\Exception $e) {
            return $this->error('Failed to sync exchange rates: ' . $e->getMessage());
        }
    }

    /** Format amount in currency. */
    public function format(FormatAmountRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $formatted = $this->queryService->format($validated['amount'], $validated['currency_code']);
            return $this->success(['formatted' => $formatted]);
        } catch (\Exception $e) {
            return $this->error('Failed to format amount: ' . $e->getMessage());
        }
    }

    /** Get supported currencies from API. */
    public function supported(): JsonResponse
    {
        try {
            $currencies = $this->queryService->getSupportedCurrencies();
            return $this->success($currencies);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve supported currencies: ' . $e->getMessage());
        }
    }
}
