<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Menu\Application\Services\MenuCommandService;
use Modules\Menu\Application\Services\MenuQueryService;
use Modules\Menu\Http\Resources\MenuItemResource;

class MenuItemController extends BaseController
{
    public function __construct(
        protected MenuQueryService $queryService,
        protected MenuCommandService $commandService
    ) {
    }

    public function show(string $id): JsonResponse
    {
        $item = $this->queryService->findItem($id);
        if (!$item) {
            return $this->notFound('Menu item not found');
        }
        return $this->success(new MenuItemResource($item));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $item = $this->queryService->findItem($id);
        if (!$item) {
            return $this->notFound('Menu item not found');
        }
        $item = $this->commandService->updateItem($id, $request->all());
        return $this->success(new MenuItemResource($item));
    }

    public function destroy(string $id): JsonResponse
    {
        $item = $this->queryService->findItem($id);
        if (!$item) {
            return $this->notFound('Menu item not found');
        }
        $this->commandService->deleteItem($id);
        return $this->success(null, 'Menu item deleted');
    }
}
