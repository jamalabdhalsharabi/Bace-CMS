<?php

declare(strict_types=1);

namespace Modules\Media\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

/**
 * Media Query Service.
 *
 * Handles all read operations for media via Repository pattern.
 * No write operations - delegates to MediaCommandService for mutations.
 *
 * @package Modules\Media\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MediaQueryService
{
    /**
     * Create a new MediaQueryService instance.
     *
     * @param MediaRepository $repository The media repository
     */
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    /**
     * Get paginated list of media.
     *
     * @param array<string, mixed> $filters Available filters: folder_id, type, search
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Media>
     */
    public function list(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Get paginated media (alias for list).
     *
     * @param int $perPage Number of items per page
     * @param array<string, mixed> $filters Filter criteria
     *
     * @return LengthAwarePaginator<Media>
     */
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Find media by ID.
     *
     * @param string $id The media UUID
     *
     * @return Media|null
     */
    public function find(string $id): ?Media
    {
        return $this->repository->find($id);
    }

    /**
     * Find media by ID (alias for find).
     *
     * @param string $id The media UUID
     *
     * @return Media|null
     */
    public function findById(string $id): ?Media
    {
        return $this->repository->find($id);
    }

    /**
     * Find media by ID including trashed.
     *
     * @param string $id The media UUID
     *
     * @return Media|null
     */
    public function findByIdWithTrashed(string $id): ?Media
    {
        return Media::withTrashed()->find($id);
    }

    /**
     * Get media by folder.
     *
     * @param string|null $folderId The folder ID (null for root)
     *
     * @return Collection<int, Media>
     */
    public function getByFolder(?string $folderId): Collection
    {
        return $this->repository->getByFolder($folderId);
    }

    /**
     * Get recent images.
     *
     * @param int $limit Maximum number of images
     *
     * @return Collection<int, Media>
     */
    public function getImages(int $limit = 50): Collection
    {
        return $this->repository->getImages($limit);
    }

    /**
     * Get storage statistics.
     *
     * @return array{total_size: int, count: int, images_count: int, videos_count: int}
     */
    public function getStats(): array
    {
        return $this->repository->getStorageStats();
    }

    /**
     * Search media.
     *
     * @param string $query Search query
     * @param array<string, mixed> $filters Additional filters
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Media>
     */
    public function search(string $query, array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $filters['search'] = $query;
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Analyze media usage across the system.
     *
     * @param string $id The media UUID
     *
     * @return array<string, mixed>
     */
    public function analyzeUsage(string $id): array
    {
        // This would check various tables for media references
        return [
            'used_in' => [],
            'reference_count' => 0,
        ];
    }
}
