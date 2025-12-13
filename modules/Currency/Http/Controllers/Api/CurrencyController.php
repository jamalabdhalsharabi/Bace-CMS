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
        $currencies = $this->queryService->getActive();

        return $this->success(CurrencyResource::collection($currencies));
    }

    /**
     * Display the specified currency by its UUID.
     *
     * @param string $id The UUID of the currency
     * @return JsonResponse The currency data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $currency = $this->queryService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        return $this->success(new CurrencyResource($currency));
    }

    /**
     * Store a newly created currency.
     *
     * @param CreateCurrencyRequest $request The validated request
     * @return JsonResponse The created currency (HTTP 201)
     */
    public function store(CreateCurrencyRequest $request): JsonResponse
    {
        $currency = $this->queryService->create($request->validated());

        return $this->created(new CurrencyResource($currency));
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
        $currency = $this->queryService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        $currency = $this->queryService->update($currency, $request->validated());

        return $this->success(new CurrencyResource($currency));
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
        $currency = $this->queryService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        try {
            $this->queryService->delete($currency);

            return $this->success(null, 'Currency deleted');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param ConvertCurrencyRequest $request The request with amount, from, and to
     * @return JsonResponse Converted amount and formatted value
     * @throws \RuntimeException If conversion fails
     */
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
}
