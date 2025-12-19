<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Menu\Domain\Contracts\MenuRepositoryInterface;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Menu Repository Implementation.
 *
 * Read-only repository for Menu model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Menu>
 * @implements MenuRepositoryInterface
 *
 * @package Modules\Menu\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    /**
     * Create a new MenuRepository instance.
     *
     * @param Menu $model The Menu model instance
     */
    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    /**
     * Get menu by location with eager-loaded items.
     *
     * Uses eager loading for items and their children to prevent N+1 queries.
     *
     * @param string $location The menu location (e.g., 'header', 'footer')
     *
     * @return Menu|null
     */
    public function getByLocation(string $location): ?Menu
    {
        return $this->query()
            ->where('location', $location)
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->whereNull('parent_id')->with('children')->ordered()])
            ->first();
    }

    /**
     * Get all active menus with their items.
     *
     * @return Collection<int, Menu>
     */
    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->with('items')
            ->get();
    }

    /**
     * Get all available menu locations from configuration.
     *
     * @return array<string, string>
     */
    public function getAllLocations(): array
    {
        return config('menu.locations', [
            'header' => 'Header Menu',
            'footer' => 'Footer Menu',
            'sidebar' => 'Sidebar Menu',
        ]);
    }

    /**
     * Get all menus with their items.
     *
     * @return Collection<int, Menu>
     */
    public function getAll(): Collection
    {
        return $this->query()
            ->with(['items.children'])
            ->get();
    }

    /**
     * Find menu by slug.
     *
     * @param string $slug The menu slug
     *
     * @return Menu|null
     */
    public function findBySlug(string $slug): ?Menu
    {
        return $this->query()
            ->where('slug', $slug)
            ->with(['items.children'])
            ->first();
    }

    /**
     * Find a menu item by ID.
     *
     * @param string $itemId The menu item ID
     *
     * @return MenuItem|null
     */
    public function findItem(string $itemId): ?MenuItem
    {
        return MenuItem::with('children')->find($itemId);
    }
}
