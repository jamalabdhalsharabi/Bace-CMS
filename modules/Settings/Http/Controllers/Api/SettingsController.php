<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Settings\Contracts\SettingsServiceContract;
use Modules\Settings\Http\Requests\UpdateSettingsRequest;

class SettingsController extends BaseController
{
    public function __construct(
        protected SettingsServiceContract $settingsService
    ) {}

    public function public(): JsonResponse
    {
        return $this->success($this->settingsService->public());
    }

    public function index(): JsonResponse
    {
        return $this->success($this->settingsService->all());
    }

    public function group(string $group): JsonResponse
    {
        return $this->success($this->settingsService->group($group));
    }

    public function show(string $key): JsonResponse
    {
        if (!$this->settingsService->has($key)) {
            return $this->notFound('Setting not found');
        }

        return $this->success([
            'key' => $key,
            'value' => $this->settingsService->get($key),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settingsService->setMany($request->validated()['settings']);

        return $this->success(
            $this->settingsService->all(),
            'Settings updated successfully'
        );
    }

    public function destroy(string $key): JsonResponse
    {
        if (!$this->settingsService->forget($key)) {
            return $this->notFound('Setting not found');
        }

        return $this->success(null, 'Setting deleted');
    }

    public function clearCache(): JsonResponse
    {
        $this->settingsService->clearCache();

        return $this->success(null, 'Settings cache cleared');
    }
}
