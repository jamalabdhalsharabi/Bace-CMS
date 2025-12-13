<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Services;

use Modules\Menu\Application\Actions\CreateMenuAction;
use Modules\Menu\Application\Actions\DeleteMenuAction;
use Modules\Menu\Application\Actions\ManageMenuItemAction;
use Modules\Menu\Application\Actions\ToggleMenuAction;
use Modules\Menu\Application\Actions\UpdateMenuAction;
use Modules\Menu\Domain\DTO\MenuData;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Menu Command Service.
 */
final class MenuCommandService
{
    public function __construct(
        private readonly CreateMenuAction $createAction,
        private readonly UpdateMenuAction $updateAction,
        private readonly DeleteMenuAction $deleteAction,
        private readonly ToggleMenuAction $toggleAction,
        private readonly ManageMenuItemAction $manageItemAction,
    ) {}

    public function create(MenuData $data): Menu
    {
        return $this->createAction->execute($data);
    }

    public function update(Menu $menu, MenuData $data): Menu
    {
        return $this->updateAction->execute($menu, $data);
    }

    public function delete(Menu $menu): bool
    {
        return $this->deleteAction->execute($menu);
    }

    public function activate(Menu $menu): Menu
    {
        return $this->toggleAction->activate($menu);
    }

    public function deactivate(Menu $menu): Menu
    {
        return $this->toggleAction->deactivate($menu);
    }

    public function addItem(Menu $menu, array $data): MenuItem
    {
        return $this->manageItemAction->add($menu, $data);
    }

    public function updateItem(MenuItem $item, array $data): MenuItem
    {
        return $this->manageItemAction->update($item, $data);
    }

    public function deleteItem(MenuItem $item): bool
    {
        return $this->manageItemAction->delete($item);
    }

    public function reorderItems(Menu $menu, array $order): Menu
    {
        return $this->manageItemAction->reorder($menu, $order);
    }
}
