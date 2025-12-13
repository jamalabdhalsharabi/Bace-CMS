<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\ExchangeRates\Application\Services\ExchangeRateCommandService;
use Modules\ExchangeRates\Application\Services\ExchangeRateQueryService;
use Modules\ExchangeRates\Http\Resources\ExchangeRateResource;

class ExchangeRateController extends BaseController
{
    public function __construct(
        protected ExchangeRateQueryService $queryService,
        protected ExchangeRateCommandService $commandService
    ) {
    }

    /**
     * Display all exchange rates.
     *
     * @return JsonResponse Collection of exchange rates
     */
    public function index(): JsonResponse
    {
        return $this->success(ExchangeRateResource::collection($this->queryService->getAllRates()));
    }

    /**
     * Display a specific exchange rate between two currencies.
     *
     * @param string $baseId The base currency UUID
     * @param string $targetId The target currency UUID
     * @return JsonResponse The exchange rate or 404 error
     */
    public function show(string $baseId, string $targetId): JsonResponse
    {
        $rate = $this->queryService->getRate($baseId, $targetId);
        return $rate ? $this->success(new ExchangeRateResource($rate)) : $this->notFound('Rate not found');
    }

    /**
     * Fetch latest exchange rates from external API provider.
     *
     * @param Request $request The request containing optional provider name
     * @return JsonResponse Fetch result with success/error status
     */
    public function fetch(Request $request): JsonResponse
    {
        $result = $this->queryService->fetchFromApi($request->provider);
        return $result['success'] 
            ? $this->success($result, 'Rates fetched successfully')
            : $this->error($result['error'], 500);
    }

    /**
     * Manually update an exchange rate.
     *
     * @param Request $request The request containing currencies and rate value
     * @return JsonResponse The updated exchange rate
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'base_currency_id' => 'required|uuid|exists:currencies,id',
            'target_currency_id' => 'required|uuid|exists:currencies,id',
            'rate' => 'required|numeric|min:0.000001',
        ]);

        $rate = $this->queryService->updateManually(
            $request->base_currency_id,
            $request->target_currency_id,
            $request->rate
        );

        return $this->success(new ExchangeRateResource($rate), 'Rate updated');
    }

    /**
     * Freeze an exchange rate to prevent automatic updates.
     *
     * @param string $id The exchange rate UUID
     * @return JsonResponse The frozen rate or 404 error
     */
    public function freeze(string $id): JsonResponse
    {
        $rate = ExchangeRate::find($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->queryService->freeze($rate)));
    }

    /**
     * Unfreeze an exchange rate to allow automatic updates.
     *
     * @param string $id The exchange rate UUID
     * @return JsonResponse The unfrozen rate or 404 error
     */
    public function unfreeze(string $id): JsonResponse
    {
        $rate = ExchangeRate::find($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->queryService->unfreeze($rate)));
    }

    /**
     * Get historical exchange rate data for a currency pair.
     *
     * @param Request $request The request containing optional from/to date filters
     * @param string $baseId The base currency UUID
     * @param string $targetId The target currency UUID
     * @return JsonResponse Historical rate data
     */
    public function history(Request $request, string $baseId, string $targetId): JsonResponse
    {
        $history = $this->queryService->getHistory($baseId, $targetId, $request->from, $request->to);
        return $this->success($history);
    }

    /**
     * Clean old historical rate data.
     *
     * @param Request $request The request containing optional days parameter
     * @return JsonResponse Count of deleted records
     */
    public function cleanHistory(Request $request): JsonResponse
    {
        $deleted = $this->queryService->cleanOldHistory($request->integer('days', 365));
        return $this->success(['deleted' => $deleted], 'Old history cleaned');
    }

    /**
     * Import historical exchange rate data.
     *
     * @param Request $request The request containing history data array
     * @return JsonResponse Import result
     */
    public function importHistory(Request $request): JsonResponse
    {
        $request->validate(['data' => 'required|array']);
        $result = $this->queryService->importHistory($request->data);
        return $this->success($result);
    }

    /**
     * Export historical exchange rate data for a currency pair.
     *
     * @param string $baseId The base currency UUID
     * @param string $targetId The target currency UUID
     * @return JsonResponse Exported history data
     */
    public function exportHistory(string $baseId, string $targetId): JsonResponse
    {
        return $this->success($this->queryService->exportHistory($baseId, $targetId));
    }

    /**
     * Create a rate alert for notifications when rates meet conditions.
     *
     * @param Request $request The request containing alert configuration
     * @return JsonResponse The created alert (HTTP 201)
     */
    public function createAlert(Request $request): JsonResponse
    {
        $request->validate([
            'base_currency_id' => 'required|uuid|exists:currencies,id',
            'target_currency_id' => 'required|uuid|exists:currencies,id',
            'condition' => 'required|in:above,below,equals',
            'threshold' => 'required|numeric|min:0',
        ]);

        return $this->created($this->queryService->createAlert($request->all()));
    }

    /**
     * Deactivate a rate alert.
     *
     * @param string $id The alert UUID
     * @return JsonResponse The deactivated alert or 404 error
     */
    public function deactivateAlert(string $id): JsonResponse
    {
        $alert = RateAlert::find($id);
        if (!$alert) return $this->notFound('Alert not found');
        return $this->success($this->queryService->deactivateAlert($alert));
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param Request $request The request containing amount and currency IDs
     * @return JsonResponse Converted amount
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency_id' => 'required|uuid|exists:currencies,id',
            'to_currency_id' => 'required|uuid|exists:currencies,id',
        ]);

        $result = $this->queryService->convert(
            $request->amount,
            $request->from_currency_id,
            $request->to_currency_id
        );

        return $this->success(['converted_amount' => $result]);
    }

    /**
     * Detect conflicting exchange rates.
     *
     * @return JsonResponse Array of detected conflicts
     */
    public function detectConflicts(): JsonResponse
    {
        return $this->success($this->queryService->detectConflicts());
    }

    /**
     * Update product prices based on exchange rate changes.
     *
     * @param Request $request The request containing currency_id
     * @return JsonResponse Count of updated products
     */
    public function updateProductPrices(Request $request): JsonResponse
    {
        $request->validate(['currency_id' => 'required|uuid|exists:currencies,id']);
        $updated = $this->queryService->updateProductPrices($request->currency_id);
        return $this->success(['updated' => $updated]);
    }
}
