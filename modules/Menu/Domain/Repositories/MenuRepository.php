<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Menu\Domain\Models\Menu;

/**
 * Menu Repository.
 *
 * @extends BaseRepository<Menu>
 */
final class MenuRepository extends BaseRepository
{
    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    public function getByLocation(string $location): ?Menu
    {
        return $this->query()
            ->where('location', $location)
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->whereNull('parent_id')->with('children')->ordered()])
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->with('items')
            ->get();
    }

    public function getAllLocations(): array
    {
        return config('menu.locations', [
            'header' => 'Header Menu',
            'footer' => 'Footer Menu',
            'sidebar' => 'Sidebar Menu',
        ]);
    }
}
