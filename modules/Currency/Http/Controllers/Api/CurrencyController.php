<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Currency\Contracts\CurrencyServiceContract;
use Modules\Currency\Http\Requests\ConvertCurrencyRequest;
use Modules\Currency\Http\Requests\CreateCurrencyRequest;
use Modules\Currency\Http\Requests\UpdateCurrencyRequest;
use Modules\Currency\Http\Resources\CurrencyResource;

class CurrencyController extends BaseController
{
    public function __construct(
        protected CurrencyServiceContract $currencyService
    ) {}

    public function index(): JsonResponse
    {
        $currencies = $this->currencyService->getActive();

        return $this->success(CurrencyResource::collection($currencies));
    }

    public function show(string $id): JsonResponse
    {
        $currency = $this->currencyService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        return $this->success(new CurrencyResource($currency));
    }

    public function store(CreateCurrencyRequest $request): JsonResponse
    {
        $currency = $this->currencyService->create($request->validated());

        return $this->created(new CurrencyResource($currency));
    }

    public function update(UpdateCurrencyRequest $request, string $id): JsonResponse
    {
        $currency = $this->currencyService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        $currency = $this->currencyService->update($currency, $request->validated());

        return $this->success(new CurrencyResource($currency));
    }

    public function destroy(string $id): JsonResponse
    {
        $currency = $this->currencyService->find($id);

        if (!$currency) {
            return $this->notFound('Currency not found');
        }

        try {
            $this->currencyService->delete($currency);

            return $this->success(null, 'Currency deleted');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function convert(ConvertCurrencyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $converted = $this->currencyService->convert(
                (float) $validated['amount'],
                $validated['from'],
                $validated['to']
            );

            return $this->success([
                'amount' => $validated['amount'],
                'from' => $validated['from'],
                'to' => $validated['to'],
                'converted' => $converted,
                'formatted' => $this->currencyService->format($converted, $validated['to']),
            ]);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
