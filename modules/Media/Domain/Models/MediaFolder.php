<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class MediaFolder
 *
 * Eloquent model representing a media folder
 * with hierarchy and media organization.
 *
 * @package Modules\Media\Domain\Models
 *
 * @property string $id
 * @property string|null $parent_id
 * @property string $name
 * @property string $slug
 *
 * @property-read MediaFolder|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|MediaFolder[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|Media[] $media
 * @property-read string $path
 */
class MediaFolder extends Model
{
    use HasUuids;

    protected $table = 'media_folders';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function getPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode('/', $path);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
