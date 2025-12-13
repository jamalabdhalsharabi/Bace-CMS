<?php

declare(strict_types=1);

namespace Modules\Services\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Services\Domain\Models\Service;
use Modules\Services\Domain\Repositories\ServiceRepository;

final class PublishServiceAction extends Action
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function execute(Service $service): Service
    {
        $this->repository->update($service->id, [
            'status' => 'published',
            'published_at' => $service->published_at ?? now(),
        ]);

        return $service->fresh();
    }

    public function unpublish(Service $service): Service
    {
        $this->repository->update($service->id, ['status' => 'draft']);

        return $service->fresh();
    }

    public function archive(Service $service): Service
    {
        $this->repository->update($service->id, ['status' => 'archived']);

        return $service->fresh();
    }
}
