<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

/**
 * Static Block Query Service - handles all read operations.
 */
final class StaticBlockQueryService
{
    public function __construct(
        private readonly StaticBlockRepository $repository
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getByType(string $type): Collection
    {
        return $this->repository->getByType($type);
    }

    public function find(string $id): ?StaticBlock
    {
        return $this->repository->find($id);
    }

    public function findWithTrashed(string $id): ?StaticBlock
    {
        return $this->repository->query()->withTrashed()->find($id);
    }

    public function findByIdentifier(string $identifier): ?StaticBlock
    {
        return $this->repository->findByIdentifier($identifier);
    }

    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getTrashed($perPage);
    }

    public function findUsages(string $blockId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('page_static_blocks')
            ->join('pages', 'pages.id', '=', 'page_static_blocks.page_id')
            ->where('static_block_id', $blockId)
            ->select('pages.id', 'pages.slug', 'page_static_blocks.position')
            ->get();
    }
}
