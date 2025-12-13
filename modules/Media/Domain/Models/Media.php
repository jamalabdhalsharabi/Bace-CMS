<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Class Media
 *
 * Eloquent model representing a media file (image, video, document)
 * with storage, conversions, and metadata.
 *
 * @package Modules\Media\Domain\Models
 *
 * @property string $id
 * @property string|null $folder_id
 * @property string|null $user_id
 * @property string|null $mediable_type
 * @property string|null $mediable_id
 * @property string $collection
 * @property string $disk
 * @property string $path
 * @property string $filename
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size
 * @property array|null $dimensions
 * @property array|null $meta
 * @property string|null $alt_text
 * @property string|null $title
 * @property bool $is_featured
 * @property int $ordering
 *
 * @property-read MediaFolder|null $folder
 * @property-read string $url
 * @property-read string $full_path
 * @property-read string $human_readable_size
 */
class Media extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'folder_id',
        'user_id',
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'path',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'dimensions',
        'meta',
        'alt_text',
        'title',
        'is_featured',
        'ordering',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'size' => 'integer',
        'ordering' => 'integer',
    ];

    /**
     * Define the belongs-to relationship with the media folder.
     *
     * Retrieves the folder that organizes this media file.
     * Null if the media is in the root directory.
     *
     * @return BelongsTo The belongs-to relationship instance to MediaFolder
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Define the belongs-to relationship with the uploader.
     *
     * Retrieves the User model who uploaded this media file.
     * Used for ownership and permission tracking.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Define the polymorphic relationship to the owning entity.
     *
     * Retrieves the model instance that this media is attached to
     * (Article, Product, etc.). Enables media attachment to any model.
     *
     * @return MorphTo The morph-to relationship instance
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor for the media file's public URL.
     *
     * Generates the full URL to access this media file via the
     * configured storage disk.
     *
     * @return string The fully qualified URL to the media file
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Accessor for the media file's absolute filesystem path.
     *
     * Returns the full server path to the file for filesystem
     * operations like copying or processing.
     *
     * @return string The absolute filesystem path to the media file
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    /**
     * Get the URL for a specific conversion of the media.
     *
     * Returns the URL to a converted version (thumbnail, medium, etc.)
     * if it exists, otherwise falls back to the original URL.
     *
     * @param string $conversion The conversion name (e.g., 'thumb', 'medium')
     *
     * @return string The URL to the converted or original media file
     */
    public function getUrl(string $conversion = ''): string
    {
        if (empty($conversion)) {
            return $this->url;
        }

        $conversionPath = $this->getConversionPath($conversion);

        if (Storage::disk($this->disk)->exists($conversionPath)) {
            return Storage::disk($this->disk)->url($conversionPath);
        }

        return $this->url;
    }

    /**
     * Generate the storage path for a media conversion.
     *
     * Constructs the expected path where a converted version
     * of this media file would be stored.
     *
     * @param string $conversion The conversion name to generate path for
     *
     * @return string The relative storage path for the conversion
     */
    public function getConversionPath(string $conversion): string
    {
        $pathInfo = pathinfo($this->path);

        return $pathInfo['dirname'] . '/conversions/' . $pathInfo['filename'] . '-' . $conversion . '.' . $pathInfo['extension'];
    }

    /**
     * Determine if the media file is an image.
     *
     * Checks if the MIME type starts with 'image/'.
     * Used for conditional rendering and processing.
     *
     * @return bool True if the media is an image, false otherwise
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Determine if the media file is a video.
     *
     * Checks if the MIME type starts with 'video/'.
     * Used for conditional rendering and player display.
     *
     * @return bool True if the media is a video, false otherwise
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Determine if the media file is an audio file.
     *
     * Checks if the MIME type starts with 'audio/'.
     * Used for conditional rendering and player display.
     *
     * @return bool True if the media is audio, false otherwise
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Determine if the media file is a document.
     *
     * Checks if the MIME type matches common document formats
     * (PDF, Word, Excel). Used for icon display and handling.
     *
     * @return bool True if the media is a document, false otherwise
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Get the width of the media in pixels.
     *
     * Returns the width from the dimensions array for images.
     * Returns null for non-image files or if dimensions not set.
     *
     * @return int|null The width in pixels or null if not available
     */
    public function getWidth(): ?int
    {
        return $this->dimensions['width'] ?? null;
    }

    /**
     * Get the height of the media in pixels.
     *
     * Returns the height from the dimensions array for images.
     * Returns null for non-image files or if dimensions not set.
     *
     * @return int|null The height in pixels or null if not available
     */
    public function getHeight(): ?int
    {
        return $this->dimensions['height'] ?? null;
    }

    /**
     * Accessor for the file size in human-readable format.
     *
     * Converts the raw byte size to a formatted string with
     * appropriate units (B, KB, MB, GB).
     *
     * @return string The formatted file size (e.g., '1.5 MB')
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Query scope to filter only image files.
     *
     * Filters media where MIME type starts with 'image/'.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'LIKE', 'image/%');
    }

    /**
     * Query scope to filter only video files.
     *
     * Filters media where MIME type starts with 'video/'.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'LIKE', 'video/%');
    }

    /**
     * Query scope to filter media by folder.
     *
     * Filters media in the specified folder. Pass null for root folder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string|null $folderId The folder UUID or null for root
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeInFolder($query, ?string $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Query scope to filter media by collection name.
     *
     * Filters media belonging to the specified collection
     * (e.g., 'avatars', 'gallery', 'documents').
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $collection The collection name to filter by
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeForCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }
}
