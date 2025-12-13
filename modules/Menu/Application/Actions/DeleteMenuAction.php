<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Repositories\MenuRepository;

final class DeleteMenuAction extends Action
{
    public function __construct(
        private readonly MenuRepository $repository
    ) {}

    public function execute(Menu $menu): bool
    {
        $menu->items()->delete();

        return $this->repository->delete($menu->id);
    }
}
