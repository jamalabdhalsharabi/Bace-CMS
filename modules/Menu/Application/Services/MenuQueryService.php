<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

/**
 * Menu Query Service.
 *
 * Handles all read operations for menus via Repository pattern.
 * No write operations - delegates to MenuCommandService for mutations.
 *
 * @package Modules\Menu\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MenuQueryService
{
    /**
     * Create a new MenuQueryService instance.
     *
     * @param MenuRepository $repository The menu repository
     */
    public function __construct(
        private readonly MenuRepository $repository
    ) {}

    public function list(): Collection
    {
        return $this->repository
            ->with(['items'])
            ->all();
    }

    public function find(string $id): ?Menu
    {
        return $this->repository
            ->with(['items.children'])
            ->find($id);
    }

    public function getByLocation(string $location): ?Menu
    {
        return $this->repository->getByLocation($location);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getAllLocations(): array
    {
        return $this->repository->getAllLocations();
    }

    public function getAll(): Collection
    {
        return $this->repository->with(['items.children'])->all();
    }

    public function findBySlug(string $slug): ?Menu
    {
        return Menu::where('slug', $slug)->with(['items.children'])->first();
    }

    public function findByLocation(string $location): ?Menu
    {
        return $this->getByLocation($location);
    }

    public function findItem(string $itemId): ?\Modules\Menu\Domain\Models\MenuItem
    {
        return \Modules\Menu\Domain\Models\MenuItem::find($itemId);
    }
}
