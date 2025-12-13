<?php

declare(strict_types=1);

namespace Modules\Services\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Services\Domain\Models\Service;
use Modules\Services\Domain\Repositories\ServiceRepository;

final class DeleteServiceAction extends Action
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function execute(Service $service): bool
    {
        $service->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($service->id);
    }
}
