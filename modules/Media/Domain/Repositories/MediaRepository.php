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
 * @extends BaseRepository<Media>
 */
final class MediaRepository extends BaseRepository
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

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

    public function getImages(int $limit = 50): Collection
    {
        return $this->query()
            ->where('mime_type', 'LIKE', 'image/%')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
