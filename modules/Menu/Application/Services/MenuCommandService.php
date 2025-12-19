<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Services;

use Modules\Menu\Application\Actions\CreateMenuAction;
use Modules\Menu\Application\Actions\DeleteMenuAction;
use Modules\Menu\Application\Actions\DeleteMenuItemAction;
use Modules\Menu\Application\Actions\ManageMenuItemAction;
use Modules\Menu\Application\Actions\ReorderMenuItemsAction;
use Modules\Menu\Application\Actions\ToggleMenuAction;
use Modules\Menu\Application\Actions\UpdateMenuAction;
use Modules\Menu\Application\Actions\UpdateMenuItemAction;
use Modules\Menu\Domain\DTO\MenuData;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Menu Command Service.
 *
 * Orchestrates all write operations via Action classes.
 * No direct Repository or Model usage - delegates to Actions only.
 *
 * @package Modules\Menu\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MenuCommandService
{
    /**
     * @param CreateMenuAction $createAction Action for creating menus
     * @param UpdateMenuAction $updateAction Action for updating menus
     * @param DeleteMenuAction $deleteAction Action for deleting menus
     * @param ToggleMenuAction $toggleAction Action for activating/deactivating menus
     * @param ManageMenuItemAction $manageItemAction Action for adding menu items
     * @param UpdateMenuItemAction $updateItemAction Action for updating menu items
     * @param DeleteMenuItemAction $deleteItemAction Action for deleting menu items
     * @param ReorderMenuItemsAction $reorderAction Action for reordering menu items
     */
    public function __construct(
        private readonly CreateMenuAction $createAction,
        private readonly UpdateMenuAction $updateAction,
        private readonly DeleteMenuAction $deleteAction,
        private readonly ToggleMenuAction $toggleAction,
        private readonly ManageMenuItemAction $manageItemAction,
        private readonly UpdateMenuItemAction $updateItemAction,
        private readonly DeleteMenuItemAction $deleteItemAction,
        private readonly ReorderMenuItemsAction $reorderAction,
    ) {}

    /**
     * Create a new menu.
     *
     * @param MenuData $data The menu data
     *
     * @return Menu The created menu
     */
    public function create(MenuData $data): Menu
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing menu.
     *
     * @param Menu $menu The menu to update
     * @param MenuData $data The updated data
     *
     * @return Menu The updated menu
     */
    public function update(Menu $menu, MenuData $data): Menu
    {
        return $this->updateAction->execute($menu, $data);
    }

    /**
     * Delete a menu.
     *
     * @param Menu $menu The menu to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Menu $menu): bool
    {
        return $this->deleteAction->execute($menu);
    }

    /**
     * Activate a menu.
     *
     * @param Menu $menu The menu to activate
     *
     * @return Menu The activated menu
     */
    public function activate(Menu $menu): Menu
    {
        return $this->toggleAction->activate($menu);
    }

    /**
     * Deactivate a menu.
     *
     * @param Menu $menu The menu to deactivate
     *
     * @return Menu The deactivated menu
     */
    public function deactivate(Menu $menu): Menu
    {
        return $this->toggleAction->deactivate($menu);
    }

    /**
     * Add an item to a menu.
     *
     * @param Menu $menu The menu to add the item to
     * @param array<string, mixed> $data The item data
     *
     * @return MenuItem The created menu item
     */
    public function addItem(Menu $menu, array $data): MenuItem
    {
        return $this->manageItemAction->add($menu, $data);
    }

    /**
     * Update a menu item.
     *
     * @param string $itemId The ID of the item to update
     * @param array<string, mixed> $data The updated data
     *
     * @return MenuItem The updated menu item
     */
    public function updateItem(string $itemId, array $data): MenuItem
    {
        return $this->updateItemAction->execute($itemId, $data);
    }

    /**
     * Delete a menu item.
     *
     * @param string $itemId The ID of the item to delete
     *
     * @return bool True if deletion was successful
     */
    public function deleteItem(string $itemId): bool
    {
        return $this->deleteItemAction->execute($itemId);
    }

    /**
     * Reorder menu items.
     *
     * @param array<int, string> $order Array of item IDs in desired order
     *
     * @return void
     */
    public function reorderItems(array $order): void
    {
        $this->reorderAction->execute($order);
    }
}
