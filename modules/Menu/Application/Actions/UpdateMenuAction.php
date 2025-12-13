<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\DTO\MenuData;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

final class UpdateMenuAction extends Action
{
    public function __construct(
        private readonly MenuRepository $repository
    ) {}

    public function execute(Menu $menu, MenuData $data): Menu
    {
        return $this->transaction(function () use ($menu, $data) {
            $this->repository->update($menu->id, [
                'name' => $data->name,
                'location' => $data->location,
                'is_active' => $data->is_active,
            ]);

            if (!empty($data->items)) {
                $menu->items()->delete();
                $this->syncItems($menu, $data->items);
            }

            return $menu->fresh(['items']);
        });
    }

    private function syncItems(Menu $menu, array $items, ?string $parentId = null): void
    {
        foreach ($items as $index => $itemData) {
            $item = $menu->items()->create([
                'parent_id' => $parentId,
                'type' => $itemData['type'] ?? 'custom',
                'title' => $itemData['title'],
                'url' => $itemData['url'] ?? null,
                'target' => $itemData['target'] ?? '_self',
                'icon' => $itemData['icon'] ?? null,
                'sort_order' => $index,
                'is_active' => $itemData['is_active'] ?? true,
            ]);

            if (!empty($itemData['children'])) {
                $this->syncItems($menu, $itemData['children'], $item->id);
            }
        }
    }
}
