<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Menu\Domain\Models\Menu;

/**
 * Menu Repository Interface.
 *
 * @extends RepositoryInterface<Menu>
 *
 * @package Modules\Menu\Domain\Contracts
 */
interface MenuRepositoryInterface extends RepositoryInterface
{
    /**
     * Get menu by location.
     */
    public function getByLocation(string $location): ?Menu;

    /**
     * Get all active menus.
     */
    public function getActive(): Collection;

    /**
     * Get all available locations.
     */
    public function getAllLocations(): array;
}
