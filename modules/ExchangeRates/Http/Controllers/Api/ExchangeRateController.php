<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\ExchangeRates\Application\Services\ExchangeRateCommandService;
use Modules\ExchangeRates\Application\Services\ExchangeRateQueryService;
use Modules\ExchangeRates\Http\Requests\ConvertRequest;
use Modules\ExchangeRates\Http\Requests\CreateAlertRequest;
use Modules\ExchangeRates\Http\Requests\ImportHistoryRequest;
use Modules\ExchangeRates\Http\Requests\UpdateProductPricesRequest;
use Modules\ExchangeRates\Http\Requests\UpdateRateRequest;
use Modules\ExchangeRates\Http\Resources\ExchangeRateResource;

/**
 * ExchangeRate API Controller.
 *
 * Follows Clean Architecture principles.
 */
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
        $result = $this->commandService->fetchFromApi($request->provider);
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
    public function update(UpdateRateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $rate = $this->commandService->updateManually(
            $data['base_currency_id'],
            $data['target_currency_id'],
            $data['rate']
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
        $rate = $this->queryService->findById($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->commandService->freeze($id)));
    }

    /**
     * Unfreeze an exchange rate to allow automatic updates.
     *
     * @param string $id The exchange rate UUID
     * @return JsonResponse The unfrozen rate or 404 error
     */
    public function unfreeze(string $id): JsonResponse
    {
        $rate = $this->queryService->findById($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->commandService->unfreeze($id)));
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
        $deleted = $this->commandService->cleanOldHistory($request->integer('days', 365));
        return $this->success(['deleted' => $deleted], 'Old history cleaned');
    }

    /**
     * Import historical exchange rate data.
     *
     * @param Request $request The request containing history data array
     * @return JsonResponse Import result
     */
    public function importHistory(ImportHistoryRequest $request): JsonResponse
    {
        $result = $this->commandService->importHistory($request->validated()['data']);
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
    public function createAlert(CreateAlertRequest $request): JsonResponse
    {
        return $this->created($this->commandService->createAlert($request->validated()));
    }

    /**
     * Deactivate a rate alert.
     *
     * @param string $id The alert UUID
     * @return JsonResponse The deactivated alert or 404 error
     */
    public function deactivateAlert(string $id): JsonResponse
    {
        $alert = $this->queryService->findAlert($id);
        if (!$alert) return $this->notFound('Alert not found');
        return $this->success($this->commandService->deactivateAlert($id));
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param Request $request The request containing amount and currency IDs
     * @return JsonResponse Converted amount
     */
    public function convert(ConvertRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->queryService->convert(
            $data['amount'],
            $data['from_currency_id'],
            $data['to_currency_id']
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
    public function updateProductPrices(UpdateProductPricesRequest $request): JsonResponse
    {
        $updated = $this->commandService->updateProductPrices($request->validated()['currency_id']);
        return $this->success(['updated' => $updated]);
    }
}
