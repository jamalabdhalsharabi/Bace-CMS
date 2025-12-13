<?php

declare(strict_types=1);

namespace Modules\Services\Application\Services;

use Modules\Services\Application\Actions\CreateServiceAction;
use Modules\Services\Application\Actions\DeleteServiceAction;
use Modules\Services\Application\Actions\DuplicateServiceAction;
use Modules\Services\Application\Actions\PublishServiceAction;
use Modules\Services\Application\Actions\ReorderServiceAction;
use Modules\Services\Application\Actions\UpdateServiceAction;
use Modules\Services\Domain\DTO\ServiceData;
use Modules\Services\Domain\Models\Service;

/**
 * Service Command Service.
 */
final class ServiceCommandService
{
    public function __construct(
        private readonly CreateServiceAction $createAction,
        private readonly UpdateServiceAction $updateAction,
        private readonly DeleteServiceAction $deleteAction,
        private readonly PublishServiceAction $publishAction,
        private readonly DuplicateServiceAction $duplicateAction,
        private readonly ReorderServiceAction $reorderAction,
    ) {}

    public function create(ServiceData $data): Service
    {
        return $this->createAction->execute($data);
    }

    public function update(Service $service, ServiceData $data): Service
    {
        return $this->updateAction->execute($service, $data);
    }

    public function publish(Service $service): Service
    {
        return $this->publishAction->execute($service);
    }

    public function unpublish(Service $service): Service
    {
        return $this->publishAction->unpublish($service);
    }

    public function archive(Service $service): Service
    {
        return $this->publishAction->archive($service);
    }

    public function delete(Service $service): bool
    {
        return $this->deleteAction->execute($service);
    }

    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }

    public function duplicate(Service $service): Service
    {
        return $this->duplicateAction->execute($service);
    }
}
