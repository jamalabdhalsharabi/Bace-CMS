<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

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

    public function getConversionPath(string $conversion): string
    {
        $pathInfo = pathinfo($this->path);

        return $pathInfo['dirname'] . '/conversions/' . $pathInfo['filename'] . '-' . $conversion . '.' . $pathInfo['extension'];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

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

    public function getWidth(): ?int
    {
        return $this->dimensions['width'] ?? null;
    }

    public function getHeight(): ?int
    {
        return $this->dimensions['height'] ?? null;
    }

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

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'LIKE', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'LIKE', 'video/%');
    }

    public function scopeInFolder($query, ?string $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeForCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }
}
