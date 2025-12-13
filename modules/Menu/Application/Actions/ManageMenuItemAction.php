<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

final class ManageMenuItemAction extends Action
{
    public function add(Menu $menu, array $data): MenuItem
    {
        return $menu->items()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? 'custom',
            'title' => $data['title'],
            'url' => $data['url'] ?? null,
            'target' => $data['target'] ?? '_self',
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(MenuItem $item, array $data): MenuItem
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(MenuItem $item): bool
    {
        MenuItem::where('parent_id', $item->id)->update(['parent_id' => $item->parent_id]);

        return $item->delete();
    }

    public function reorder(Menu $menu, array $order): Menu
    {
        foreach ($order as $index => $itemId) {
            MenuItem::where('id', $itemId)->update(['sort_order' => $index]);
        }

        return $menu->fresh(['items']);
    }
}
