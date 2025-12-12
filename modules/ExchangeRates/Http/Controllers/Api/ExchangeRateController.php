<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\ExchangeRates\Contracts\ExchangeRateServiceContract;
use Modules\ExchangeRates\Domain\Models\ExchangeRate;
use Modules\ExchangeRates\Domain\Models\RateAlert;
use Modules\ExchangeRates\Http\Resources\ExchangeRateResource;

class ExchangeRateController extends BaseController
{
    public function __construct(protected ExchangeRateServiceContract $rateService) {}

    public function index(): JsonResponse
    {
        return $this->success(ExchangeRateResource::collection($this->rateService->getAllRates()));
    }

    public function show(string $baseId, string $targetId): JsonResponse
    {
        $rate = $this->rateService->getRate($baseId, $targetId);
        return $rate ? $this->success(new ExchangeRateResource($rate)) : $this->notFound('Rate not found');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result = $this->rateService->fetchFromApi($request->provider);
        return $result['success'] 
            ? $this->success($result, 'Rates fetched successfully')
            : $this->error($result['error'], 500);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'base_currency_id' => 'required|uuid|exists:currencies,id',
            'target_currency_id' => 'required|uuid|exists:currencies,id',
            'rate' => 'required|numeric|min:0.000001',
        ]);

        $rate = $this->rateService->updateManually(
            $request->base_currency_id,
            $request->target_currency_id,
            $request->rate
        );

        return $this->success(new ExchangeRateResource($rate), 'Rate updated');
    }

    public function freeze(string $id): JsonResponse
    {
        $rate = ExchangeRate::find($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->rateService->freeze($rate)));
    }

    public function unfreeze(string $id): JsonResponse
    {
        $rate = ExchangeRate::find($id);
        if (!$rate) return $this->notFound('Rate not found');
        return $this->success(new ExchangeRateResource($this->rateService->unfreeze($rate)));
    }

    public function history(Request $request, string $baseId, string $targetId): JsonResponse
    {
        $history = $this->rateService->getHistory($baseId, $targetId, $request->from, $request->to);
        return $this->success($history);
    }

    public function cleanHistory(Request $request): JsonResponse
    {
        $deleted = $this->rateService->cleanOldHistory($request->integer('days', 365));
        return $this->success(['deleted' => $deleted], 'Old history cleaned');
    }

    public function importHistory(Request $request): JsonResponse
    {
        $request->validate(['data' => 'required|array']);
        $result = $this->rateService->importHistory($request->data);
        return $this->success($result);
    }

    public function exportHistory(string $baseId, string $targetId): JsonResponse
    {
        return $this->success($this->rateService->exportHistory($baseId, $targetId));
    }

    public function createAlert(Request $request): JsonResponse
    {
        $request->validate([
            'base_currency_id' => 'required|uuid|exists:currencies,id',
            'target_currency_id' => 'required|uuid|exists:currencies,id',
            'condition' => 'required|in:above,below,equals',
            'threshold' => 'required|numeric|min:0',
        ]);

        return $this->created($this->rateService->createAlert($request->all()));
    }

    public function deactivateAlert(string $id): JsonResponse
    {
        $alert = RateAlert::find($id);
        if (!$alert) return $this->notFound('Alert not found');
        return $this->success($this->rateService->deactivateAlert($alert));
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency_id' => 'required|uuid|exists:currencies,id',
            'to_currency_id' => 'required|uuid|exists:currencies,id',
        ]);

        $result = $this->rateService->convert(
            $request->amount,
            $request->from_currency_id,
            $request->to_currency_id
        );

        return $this->success(['converted_amount' => $result]);
    }

    public function detectConflicts(): JsonResponse
    {
        return $this->success($this->rateService->detectConflicts());
    }

    public function updateProductPrices(Request $request): JsonResponse
    {
        $request->validate(['currency_id' => 'required|uuid|exists:currencies,id']);
        $updated = $this->rateService->updateProductPrices($request->currency_id);
        return $this->success(['updated' => $updated]);
    }
}
