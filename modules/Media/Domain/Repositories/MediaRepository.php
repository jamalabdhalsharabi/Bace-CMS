<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Media\Domain\Models\Media;

/**
 * Media Repository.
 *
 * Read-only repository for Media model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Media>
 *
 * @package Modules\Media\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MediaRepository extends BaseRepository
{
    /**
     * Create a new MediaRepository instance.
     *
     * @param Media $model The Media model instance
     */
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated media with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: folder_id, type, search, collection
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Media>
     */
    public function getPaginated(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = $this->query();

        if (isset($filters['folder_id'])) {
            if ($filters['folder_id'] === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $filters['folder_id']);
            }
        }

        if (!empty($filters['type'])) {
            match ($filters['type']) {
                'image' => $query->where('mime_type', 'LIKE', 'image/%'),
                'video' => $query->where('mime_type', 'LIKE', 'video/%'),
                'document' => $query->whereIn('mime_type', ['application/pdf', 'application/msword']),
                default => null,
            };
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => 
                $q->where('filename', 'LIKE', "%{$search}%")
                  ->orWhere('original_filename', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
            );
        }

        if (!empty($filters['collection'])) {
            $query->where('collection', $filters['collection']);
        }

        return $query->latest()->paginate($perPage);
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
        $query = $this->query();
        
        if ($folderId === null) {
            $query->whereNull('folder_id');
        } else {
            $query->where('folder_id', $folderId);
        }

        return $query->get();
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
        return $this->query()
            ->where('mime_type', 'LIKE', 'image/%')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get storage usage statistics.
     *
     * @return array{total_size: int, count: int, images_count: int, videos_count: int}
     */
    public function getStorageStats(): array
    {
        $stats = $this->query()->selectRaw("
            SUM(size) as total_size,
            COUNT(*) as count,
            SUM(CASE WHEN mime_type LIKE 'image/%' THEN 1 ELSE 0 END) as images_count,
            SUM(CASE WHEN mime_type LIKE 'video/%' THEN 1 ELSE 0 END) as videos_count
        ")->first();

        return [
            'total_size' => (int) ($stats->total_size ?? 0),
            'count' => (int) ($stats->count ?? 0),
            'images_count' => (int) ($stats->images_count ?? 0),
            'videos_count' => (int) ($stats->videos_count ?? 0),
        ];
    }
}
