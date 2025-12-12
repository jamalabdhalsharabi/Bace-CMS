<?php

declare(strict_types=1);

namespace Modules\Menu\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

interface MenuServiceContract
{
    public function all(): Collection;

    public function find(string $id): ?Menu;

    public function findBySlug(string $slug): ?Menu;

    public function findByLocation(string $location): ?Menu;

    public function getTree(string $menuId): Collection;

    public function create(array $data): Menu;

    public function update(Menu $menu, array $data): Menu;

    public function delete(Menu $menu): bool;

    public function addItem(Menu $menu, array $data): MenuItem;

    public function updateItem(MenuItem $item, array $data): MenuItem;

    public function deleteItem(MenuItem $item): bool;

    public function reorderItems(array $order): void;
}
