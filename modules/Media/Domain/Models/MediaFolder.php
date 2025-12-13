<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MediaFolder Model - Represents folders for organizing media files.
 *
 * This model provides a hierarchical folder structure for organizing
 * media assets with support for nesting and file counting.
 *
 * @property string $id UUID primary key
 * @property string|null $parent_id Foreign key to parent folder
 * @property string $name Folder display name
 * @property string $slug URL-friendly folder identifier
 * @property string|null $path Full path from root (e.g., 'images/products')
 * @property int $depth Nesting depth (0 = root level)
 * @property int $files_count Cached count of files in this folder
 * @property string $created_by UUID of user who created the folder
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read MediaFolder|null $parent Parent folder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MediaFolder> $children Child folders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Media> $media Media files in this folder
 * @property-read \App\Models\User $creator User who created the folder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder root() Filter root-level folders
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder query()
 */
class MediaFolder extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_folders';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'path',
        'depth',
        'files_count',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'files_count' => 'integer',
        ];
    }

    /**
     * Get the parent folder.
     *
     * @return BelongsTo<MediaFolder, MediaFolder>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child folders.
     *
     * @return HasMany<MediaFolder>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get all media files in this folder.
     *
     * @return HasMany<Media>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    /**
     * Get the user who created this folder.
     *
     * @return BelongsTo<\App\Models\User, MediaFolder>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Scope to filter only root-level folders.
     *
     * @param \Illuminate\Database\Eloquent\Builder<MediaFolder> $query
     * @return \Illuminate\Database\Eloquent\Builder<MediaFolder>
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
