<?php

declare(strict_types=1);

namespace Modules\Media\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

/**
 * Media Query Service.
 */
final class MediaQueryService
{
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Media
    {
        return $this->repository->find($id);
    }

    public function getByFolder(?string $folderId): Collection
    {
        return $this->repository->getByFolder($folderId);
    }

    public function getImages(int $limit = 50): Collection
    {
        return $this->repository->getImages($limit);
    }
}
