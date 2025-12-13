<?php

declare(strict_types=1);

namespace Modules\Services\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Services\Domain\Repositories\ServiceRepository;

final class ReorderServiceAction extends Action
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->repository->update($id, ['sort_order' => $index]);
        }
    }
}
