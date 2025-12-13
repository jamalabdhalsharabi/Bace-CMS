<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

final class ToggleMenuAction extends Action
{
    public function __construct(
        private readonly MenuRepository $repository
    ) {}

    public function activate(Menu $menu): Menu
    {
        $this->repository->update($menu->id, ['is_active' => true]);

        return $menu->fresh();
    }

    public function deactivate(Menu $menu): Menu
    {
        $this->repository->update($menu->id, ['is_active' => false]);

        return $menu->fresh();
    }
}
