<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

/**
 * Menu Query Service.
 */
final class MenuQueryService
{
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
}
