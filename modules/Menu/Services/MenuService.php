<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Contracts\MenuServiceContract;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

class MenuService implements MenuServiceContract
{
    public function all(): Collection
    {
        return Menu::all();
    }

    public function find(string $id): ?Menu
    {
        return Menu::with('items.children')->find($id);
    }

    public function findBySlug(string $slug): ?Menu
    {
        return $this->cached("slug.{$slug}", fn () => 
            Menu::with('items.children.children')->where('slug', $slug)->active()->first()
        );
    }

    public function findByLocation(string $location): ?Menu
    {
        return $this->cached("location.{$location}", fn () => 
            Menu::with('items.children.children')->where('location', $location)->active()->first()
        );
    }

    public function getTree(string $menuId): Collection
    {
        $menu = Menu::find($menuId);
        return $menu ? $menu->getTree() : collect();
    }

    public function create(array $data): Menu
    {
        $menu = Menu::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->clearCache();

        return $menu;
    }

    public function update(Menu $menu, array $data): Menu
    {
        $menu->update([
            'name' => $data['name'] ?? $menu->name,
            'location' => $data['location'] ?? $menu->location,
            'is_active' => $data['is_active'] ?? $menu->is_active,
        ]);

        $this->clearCache();

        return $menu->fresh();
    }

    public function delete(Menu $menu): bool
    {
        $menu->allItems()->delete();
        $result = $menu->delete();
        $this->clearCache();

        return $result;
    }

    public function addItem(Menu $menu, array $data): MenuItem
    {
        $item = $menu->allItems()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? 'custom',
            'linkable_id' => $data['linkable_id'] ?? null,
            'linkable_type' => $data['linkable_type'] ?? null,
            'title' => json_encode($data['title'] ?? []),
            'url' => $data['url'] ?? null,
            'target' => $data['target'] ?? '_self',
            'icon' => $data['icon'] ?? null,
            'css_class' => $data['css_class'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'conditions' => $data['conditions'] ?? null,
        ]);

        $this->clearCache();

        return $item;
    }

    public function updateItem(MenuItem $item, array $data): MenuItem
    {
        $updateData = [];

        if (isset($data['parent_id'])) {
            $updateData['parent_id'] = $data['parent_id'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }
        if (isset($data['linkable_id'])) {
            $updateData['linkable_id'] = $data['linkable_id'];
            $updateData['linkable_type'] = $data['linkable_type'] ?? null;
        }
        if (isset($data['title'])) {
            $updateData['title'] = json_encode($data['title']);
        }
        if (isset($data['url'])) {
            $updateData['url'] = $data['url'];
        }
        if (isset($data['target'])) {
            $updateData['target'] = $data['target'];
        }
        if (isset($data['icon'])) {
            $updateData['icon'] = $data['icon'];
        }
        if (isset($data['css_class'])) {
            $updateData['css_class'] = $data['css_class'];
        }
        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }
        if (isset($data['conditions'])) {
            $updateData['conditions'] = $data['conditions'];
        }

        $item->update($updateData);
        $this->clearCache();

        return $item->fresh();
    }

    public function deleteItem(MenuItem $item): bool
    {
        MenuItem::where('parent_id', $item->id)->update(['parent_id' => $item->parent_id]);
        $result = $item->delete();
        $this->clearCache();

        return $result;
    }

    public function reorderItems(array $order): void
    {
        foreach ($order as $index => $id) {
            MenuItem::where('id', $id)->update(['ordering' => $index + 1]);
        }

        $this->clearCache();
    }

    protected function cached(string $key, callable $callback): mixed
    {
        if (!config('menu.cache.enabled', true)) {
            return $callback();
        }

        $ttl = config('menu.cache.ttl', 3600);

        return Cache::remember("menu.{$key}", $ttl, $callback);
    }

    public function clearCache(): void
    {
        Cache::forget('menu.slug.*');
        Cache::forget('menu.location.*');
    }
}
