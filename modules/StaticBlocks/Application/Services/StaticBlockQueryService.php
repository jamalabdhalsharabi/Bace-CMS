<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

final class StaticBlockQueryService
{
    public function __construct(
        private readonly StaticBlockRepository $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function findById(string $id): ?StaticBlock
    {
        return $this->repository->find($id);
    }

    public function findByIdentifier(string $identifier): ?StaticBlock
    {
        return $this->repository->findByIdentifier($identifier);
    }
}
