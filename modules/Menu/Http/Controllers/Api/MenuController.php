<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Menu\Contracts\MenuServiceContract;
use Modules\Menu\Domain\Models\MenuItem;
use Modules\Menu\Http\Requests\CreateMenuItemRequest;
use Modules\Menu\Http\Requests\CreateMenuRequest;
use Modules\Menu\Http\Requests\ReorderMenuItemsRequest;
use Modules\Menu\Http\Requests\UpdateMenuItemRequest;
use Modules\Menu\Http\Requests\UpdateMenuRequest;
use Modules\Menu\Http\Resources\MenuItemResource;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Services\MenuBuilder;

class MenuController extends BaseController
{
    public function __construct(
        protected MenuServiceContract $menuService,
        protected MenuBuilder $menuBuilder
    ) {}

    public function index(): JsonResponse
    {
        $menus = $this->menuService->all();

        return $this->success(MenuResource::collection($menus));
    }

    public function show(string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $menu = $this->menuService->findBySlug($slug);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    public function showByLocation(string $location): JsonResponse
    {
        $menu = $this->menuService->findByLocation($location);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    public function tree(string $slug): JsonResponse
    {
        $items = $this->menuBuilder->toArray($slug);

        return $this->success($items);
    }

    public function store(CreateMenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->create($request->validated());

        return $this->created(new MenuResource($menu));
    }

    public function update(UpdateMenuRequest $request, string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $menu = $this->menuService->update($menu, $request->validated());

        return $this->success(new MenuResource($menu));
    }

    public function destroy(string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $this->menuService->delete($menu);

        return $this->success(null, 'Menu deleted');
    }

    public function addItem(CreateMenuItemRequest $request, string $menuId): JsonResponse
    {
        $menu = $this->menuService->find($menuId);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $item = $this->menuService->addItem($menu, $request->validated());

        return $this->created(new MenuItemResource($item));
    }

    public function updateItem(UpdateMenuItemRequest $request, string $itemId): JsonResponse
    {
        $item = MenuItem::find($itemId);

        if (!$item) {
            return $this->notFound('Menu item not found');
        }

        $item = $this->menuService->updateItem($item, $request->validated());

        return $this->success(new MenuItemResource($item));
    }

    public function deleteItem(string $itemId): JsonResponse
    {
        $item = MenuItem::find($itemId);

        if (!$item) {
            return $this->notFound('Menu item not found');
        }

        $this->menuService->deleteItem($item);

        return $this->success(null, 'Menu item deleted');
    }

    public function reorderItems(ReorderMenuItemsRequest $request): JsonResponse
    {
        $this->menuService->reorderItems($request->validated()['order']);

        return $this->success(null, 'Items reordered');
    }
}
