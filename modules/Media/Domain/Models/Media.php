<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Media Model - Represents uploaded files and media assets.
 *
 * This model handles all types of media files including images, videos,
 * audio, documents, and archives with support for variants and metadata.
 *
 * @property string $id UUID primary key
 * @property string|null $folder_id Foreign key to media_folders table
 * @property string $filename Stored filename (may be renamed)
 * @property string $original_filename Original uploaded filename
 * @property string $mime_type MIME type (e.g., 'image/jpeg')
 * @property string $extension File extension without dot (e.g., 'jpg')
 * @property int $size File size in bytes
 * @property string $disk Storage disk name (e.g., 'public', 's3')
 * @property string $path Relative path within the disk
 * @property string|null $url Direct URL if available
 * @property string $type Media type (image, video, audio, document, archive, other)
 * @property int|null $width Image/video width in pixels
 * @property int|null $height Image/video height in pixels
 * @property int|null $duration Audio/video duration in seconds
 * @property string $status Processing status (processing, ready, failed, quarantine)
 * @property string|null $hash SHA-256 file hash for deduplication
 * @property bool $is_private Whether file requires authentication
 * @property array|null $meta Additional metadata as JSON
 * @property string $uploaded_by UUID of user who uploaded
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read MediaFolder|null $folder Parent folder
 * @property-read \App\Models\User $uploader User who uploaded the file
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MediaVariant> $variants Generated variants
 * @property-read string $full_url Complete URL to access the file
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Media images() Filter image files
 * @method static \Illuminate\Database\Eloquent\Builder|Media videos() Filter video files
 * @method static \Illuminate\Database\Eloquent\Builder|Media inFolder(?string $folderId) Filter by folder
 * @method static \Illuminate\Database\Eloquent\Builder|Media ready() Filter ready files
 * @method static \Illuminate\Database\Eloquent\Builder|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media query()
 */
class Media extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'folder_id',
        'filename',
        'original_filename',
        'mime_type',
        'extension',
        'size',
        'disk',
        'path',
        'url',
        'type',
        'width',
        'height',
        'duration',
        'status',
        'hash',
        'is_private',
        'meta',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'is_private' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Get the folder containing this media.
     *
     * @return BelongsTo<MediaFolder, Media>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Get the user who uploaded this media.
     *
     * @return BelongsTo<\App\Models\User, Media>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'uploaded_by');
    }

    /**
     * Get all generated variants of this media.
     *
     * @return HasMany<MediaVariant>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    /**
     * Get the full URL to access this media file.
     *
     * Returns the direct URL if set, otherwise generates
     * a URL from the storage disk.
     *
     * @return string The complete URL
     */
    public function getFullUrlAttribute(): string
    {
        return $this->url ?? Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Check if this media is an image.
     *
     * @return bool True if type is 'image'
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if this media is a video.
     *
     * @return bool True if type is 'video'
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Scope to filter only image files.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Media> $query
     * @return \Illuminate\Database\Eloquent\Builder<Media>
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope to filter only video files.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Media> $query
     * @return \Illuminate\Database\Eloquent\Builder<Media>
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope to filter media by folder.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Media> $query
     * @param string|null $folderId The folder ID (null for root)
     * @return \Illuminate\Database\Eloquent\Builder<Media>
     */
    public function scopeInFolder($query, ?string $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Scope to filter only ready (processed) media.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Media> $query
     * @return \Illuminate\Database\Eloquent\Builder<Media>
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }
}
