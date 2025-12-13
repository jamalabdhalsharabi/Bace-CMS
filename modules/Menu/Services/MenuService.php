<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Contracts\MenuServiceContract;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Class MenuService
 *
 * Service class for managing navigation menus
 * including items, tree structure, and reordering.
 *
 * @package Modules\Menu\Services
 */
class MenuService implements MenuServiceContract
{
    /**
     * Get all menus.
     *
     * @return Collection Collection of Menu models
     */
    public function all(): Collection
    {
        return Menu::all();
    }

    /**
     * Find a menu by its UUID.
     *
     * @param string $id The menu UUID
     *
     * @return Menu|null The found menu or null
     */
    public function find(string $id): ?Menu
    {
        return Menu::with('items.children')->find($id);
    }

    /**
     * Find a menu by its slug with caching.
     *
     * @param string $slug The menu slug
     *
     * @return Menu|null The found menu or null
     */
    public function findBySlug(string $slug): ?Menu
    {
        return $this->cached("slug.{$slug}", fn () => 
            Menu::with('items.children.children')->where('slug', $slug)->active()->first()
        );
    }

    /**
     * Find a menu by its location with caching.
     *
     * @param string $location The menu location identifier
     *
     * @return Menu|null The found menu or null
     */
    public function findByLocation(string $location): ?Menu
    {
        return $this->cached("location.{$location}", fn () => 
            Menu::with('items.children.children')->where('location', $location)->active()->first()
        );
    }

    /**
     * Get menu items as a tree structure.
     *
     * @param string $menuId The menu UUID
     *
     * @return Collection Hierarchical menu items
     */
    public function getTree(string $menuId): Collection
    {
        $menu = Menu::find($menuId);
        return $menu ? $menu->getTree() : collect();
    }

    /**
     * Create a new menu.
     *
     * @param array $data Menu data: 'slug', 'name', 'location'
     *
     * @return Menu The created menu
     */
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

    /**
     * Update an existing menu.
     *
     * @param Menu $menu The menu to update
     * @param array $data Updated data
     *
     * @return Menu The updated menu
     */
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

    /**
     * Delete a menu and all its items.
     *
     * @param Menu $menu The menu to delete
     *
     * @return bool True if successful
     */
    public function delete(Menu $menu): bool
    {
        $menu->allItems()->delete();
        $result = $menu->delete();
        $this->clearCache();

        return $result;
    }

    /**
     * Add an item to a menu.
     *
     * @param Menu $menu The menu
     * @param array $data Item data
     *
     * @return MenuItem The created menu item
     */
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

    /**
     * Update a menu item.
     *
     * @param MenuItem $item The item to update
     * @param array $data Updated data
     *
     * @return MenuItem The updated item
     */
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

    /**
     * Delete a menu item and reassign children.
     *
     * @param MenuItem $item The item to delete
     *
     * @return bool True if successful
     */
    public function deleteItem(MenuItem $item): bool
    {
        MenuItem::where('parent_id', $item->id)->update(['parent_id' => $item->parent_id]);
        $result = $item->delete();
        $this->clearCache();

        return $result;
    }

    /**
     * Reorder menu items by their IDs.
     *
     * @param array $order Array of item UUIDs in order
     *
     * @return void
     */
    public function reorderItems(array $order): void
    {
        foreach ($order as $index => $id) {
            MenuItem::where('id', $id)->update(['ordering' => $index + 1]);
        }

        $this->clearCache();
    }

    /**
     * Get cached data or execute callback.
     *
     * @param string $key Cache key
     * @param callable $callback Callback to execute if not cached
     *
     * @return mixed Cached or fresh data
     */
    protected function cached(string $key, callable $callback): mixed
    {
        if (!config('menu.cache.enabled', true)) {
            return $callback();
        }

        $ttl = config('menu.cache.ttl', 3600);

        return Cache::remember("menu.{$key}", $ttl, $callback);
    }

    /**
     * Clear all menu-related cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('menu.slug.*');
        Cache::forget('menu.location.*');
    }
}
