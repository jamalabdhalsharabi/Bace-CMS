<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MediaVariant Model - Represents processed variants of media files.
 *
 * This model stores different versions of media files such as
 * thumbnails, resized images, or converted formats.
 *
 * @property string $id UUID primary key
 * @property string $media_id Foreign key to parent media
 * @property string $name Variant name (e.g., 'thumbnail', 'medium', 'large')
 * @property string $filename Generated filename for this variant
 * @property string $path Relative path within storage disk
 * @property string|null $url Direct URL if available
 * @property int $size File size in bytes
 * @property int|null $width Image width in pixels
 * @property int|null $height Image height in pixels
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read Media $media The parent media file
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MediaVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaVariant query()
 */
class MediaVariant extends Model
{
    use HasUuids;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_variants';

    protected $fillable = [
        'media_id',
        'name',
        'filename',
        'path',
        'url',
        'size',
        'width',
        'height',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the parent media file.
     *
     * @return BelongsTo<Media, MediaVariant>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
