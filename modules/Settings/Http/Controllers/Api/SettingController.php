<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Settings\Application\Services\SettingCommandService;
use Modules\Settings\Application\Services\SettingQueryService;
use Modules\Settings\Http\Requests\UpdateSettingsRequest;

class SettingController extends BaseController
{
    public function __construct(
        protected SettingQueryService $queryService,
        protected SettingCommandService $commandService
    ) {
    }

    public function publicSettings(): JsonResponse
    {
        return $this->success($this->queryService->public());
    }

    public function index(): JsonResponse
    {
        return $this->success($this->queryService->all());
    }

    public function byGroup(string $group): JsonResponse
    {
        return $this->success($this->queryService->group($group));
    }

    public function show(string $key): JsonResponse
    {
        $value = $this->queryService->get($key);
        if ($value === null) {
            return $this->notFound('Setting not found');
        }
        return $this->success(['key' => $key, 'value' => $value]);
    }

    public function update(string $key, UpdateSettingsRequest $request): JsonResponse
    {
        $this->commandService->set($key, $request->input('value'));
        return $this->success(['key' => $key, 'value' => $request->input('value')], 'Setting updated');
    }

    public function bulkUpdate(UpdateSettingsRequest $request): JsonResponse
    {
        $this->commandService->setMany($request->validated()['settings']);
        return $this->success($this->queryService->getAll(), 'Settings updated');
    }

    public function destroy(string $key): JsonResponse
    {
        $this->commandService->delete($key);
        return $this->success(null, 'Setting deleted');
    }
}
