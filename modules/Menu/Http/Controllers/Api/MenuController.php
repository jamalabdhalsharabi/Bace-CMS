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

/**
 * Class MenuController
 * 
 * API controller for managing navigation menus
 * including items, tree structure, and reordering.
 * 
 * @package Modules\Menu\Http\Controllers\Api
 */
class MenuController extends BaseController
{
    /**
     * The menu service instance for handling menu-related business logic.
     *
     * @var MenuServiceContract
     */
    protected MenuServiceContract $menuService;

    /**
     * The menu builder instance for constructing menu trees.
     *
     * @var MenuBuilder
     */
    protected MenuBuilder $menuBuilder;

    /**
     * Create a new MenuController instance.
     *
     * @param MenuServiceContract $menuService The menu service contract implementation
     * @param MenuBuilder $menuBuilder The menu builder service
     */
    public function __construct(
        MenuServiceContract $menuService,
        MenuBuilder $menuBuilder
    ) {
        $this->menuService = $menuService;
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * Display a listing of all menus.
     *
     * @return JsonResponse Collection of menus
     */
    public function index(): JsonResponse
    {
        $menus = $this->menuService->all();

        return $this->success(MenuResource::collection($menus));
    }

    /**
     * Display the specified menu by its UUID.
     *
     * @param string $id The UUID of the menu to retrieve
     * @return JsonResponse The menu data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    /**
     * Display the specified menu by its slug.
     *
     * @param string $slug The URL-friendly slug of the menu
     * @return JsonResponse The menu data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $menu = $this->menuService->findBySlug($slug);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    /**
     * Display the menu assigned to a specific location.
     *
     * @param string $location The menu location (e.g., 'header', 'footer')
     * @return JsonResponse The menu data or 404 error
     */
    public function showByLocation(string $location): JsonResponse
    {
        $menu = $this->menuService->findByLocation($location);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        return $this->success(new MenuResource($menu));
    }

    /**
     * Get the menu items as a hierarchical tree.
     *
     * @param string $slug The menu slug
     * @return JsonResponse Menu items in tree structure
     */
    public function tree(string $slug): JsonResponse
    {
        $items = $this->menuBuilder->toArray($slug);

        return $this->success($items);
    }

    /**
     * Store a newly created menu in the database.
     *
     * @param CreateMenuRequest $request The validated request containing menu data
     * @return JsonResponse The newly created menu (HTTP 201)
     */
    public function store(CreateMenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->create($request->validated());

        return $this->created(new MenuResource($menu));
    }

    /**
     * Update the specified menu in the database.
     *
     * @param UpdateMenuRequest $request The validated request containing updated data
     * @param string $id The UUID of the menu to update
     * @return JsonResponse The updated menu or 404 error
     */
    public function update(UpdateMenuRequest $request, string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $menu = $this->menuService->update($menu, $request->validated());

        return $this->success(new MenuResource($menu));
    }

    /**
     * Delete the specified menu.
     *
     * @param string $id The UUID of the menu to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = $this->menuService->find($id);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $this->menuService->delete($menu);

        return $this->success(null, 'Menu deleted');
    }

    /**
     * Add a new item to a menu.
     *
     * @param CreateMenuItemRequest $request The validated request containing item data
     * @param string $menuId The UUID of the parent menu
     * @return JsonResponse The newly created menu item (HTTP 201)
     */
    public function addItem(CreateMenuItemRequest $request, string $menuId): JsonResponse
    {
        $menu = $this->menuService->find($menuId);

        if (!$menu) {
            return $this->notFound('Menu not found');
        }

        $item = $this->menuService->addItem($menu, $request->validated());

        return $this->created(new MenuItemResource($item));
    }

    /**
     * Update a menu item.
     *
     * @param UpdateMenuItemRequest $request The validated request containing updated item data
     * @param string $itemId The UUID of the menu item to update
     * @return JsonResponse The updated menu item or 404 error
     */
    public function updateItem(UpdateMenuItemRequest $request, string $itemId): JsonResponse
    {
        $item = MenuItem::find($itemId);

        if (!$item) {
            return $this->notFound('Menu item not found');
        }

        $item = $this->menuService->updateItem($item, $request->validated());

        return $this->success(new MenuItemResource($item));
    }

    /**
     * Delete a menu item.
     *
     * @param string $itemId The UUID of the menu item to delete
     * @return JsonResponse Success message or 404 error
     */
    public function deleteItem(string $itemId): JsonResponse
    {
        $item = MenuItem::find($itemId);

        if (!$item) {
            return $this->notFound('Menu item not found');
        }

        $this->menuService->deleteItem($item);

        return $this->success(null, 'Menu item deleted');
    }

    /**
     * Reorder menu items based on the provided order array.
     *
     * @param ReorderMenuItemsRequest $request The validated request containing order array
     * @return JsonResponse Success message
     */
    public function reorderItems(ReorderMenuItemsRequest $request): JsonResponse
    {
        $this->menuService->reorderItems($request->validated()['order']);

        return $this->success(null, 'Items reordered');
    }
}
