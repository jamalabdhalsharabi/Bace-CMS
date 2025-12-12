<?php

declare(strict_types=1);

namespace Modules\Media\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Media\Contracts\MediaServiceContract;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Jobs\GenerateMediaConversions;

class MediaService implements MediaServiceContract
{
    public function list(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = Media::query();

        if (!empty($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        } elseif (isset($filters['folder_id']) && $filters['folder_id'] === null) {
            $query->whereNull('folder_id');
        }

        if (!empty($filters['type'])) {
            match ($filters['type']) {
                'image' => $query->images(),
                'video' => $query->videos(),
                default => null,
            };
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('filename', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('original_filename', 'LIKE', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(string $id): ?Media
    {
        return Media::find($id);
    }

    public function upload(UploadedFile $file, array $options = []): Media
    {
        $disk = $options['disk'] ?? config('media.disk', 'public');
        $basePath = $options['path'] ?? config('media.path', 'media');
        $collection = $options['collection'] ?? 'default';

        $filename = $this->generateFilename($file);
        $path = $basePath . '/' . date('Y/m') . '/' . $filename;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $dimensions = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $dimensions = $this->getImageDimensions($file);
        }

        $media = Media::create([
            'folder_id' => $options['folder_id'] ?? null,
            'user_id' => $options['user_id'] ?? auth()->id(),
            'collection' => $collection,
            'disk' => $disk,
            'path' => $path,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'dimensions' => $dimensions,
            'alt_text' => $options['alt_text'] ?? null,
            'title' => $options['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);

        if ($media->isImage() && config('media.generate_conversions', true)) {
            dispatch(new GenerateMediaConversions($media));
        }

        return $media;
    }

    public function uploadMultiple(array $files, array $options = []): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded[] = $this->upload($file, $options);
            }
        }

        return $uploaded;
    }

    public function update(Media $media, array $data): Media
    {
        $media->update([
            'alt_text' => $data['alt_text'] ?? $media->alt_text,
            'title' => $data['title'] ?? $media->title,
            'meta' => array_merge($media->meta ?? [], $data['meta'] ?? []),
        ]);

        return $media->fresh();
    }

    public function delete(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);

        $this->deleteConversions($media);

        return $media->forceDelete();
    }

    public function move(Media $media, ?string $folderId): Media
    {
        $media->update(['folder_id' => $folderId]);

        return $media->fresh();
    }

    public function generateConversions(Media $media): void
    {
        if (!$media->isImage()) {
            return;
        }

        $conversions = config('media.image.conversions', []);

        foreach ($conversions as $name => $settings) {
            $this->createConversion($media, $name, $settings);
        }
    }

    protected function createConversion(Media $media, string $name, array $settings): void
    {
        $sourcePath = Storage::disk($media->disk)->path($media->path);
        $conversionPath = $media->getConversionPath($name);

        $image = $this->loadImage($sourcePath);

        if (!$image) {
            return;
        }

        $width = $settings['width'] ?? null;
        $height = $settings['height'] ?? null;
        $fit = $settings['fit'] ?? 'contain';

        $resized = $this->resizeImage($image, $width, $height, $fit);

        $quality = config('media.image.quality', 85);

        $this->saveImage($resized, Storage::disk($media->disk)->path($conversionPath), $quality);
    }

    protected function loadImage(string $path): mixed
    {
        $info = getimagesize($path);

        if (!$info) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => null,
        };
    }

    protected function resizeImage(mixed $image, ?int $width, ?int $height, string $fit): mixed
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if (!$width && !$height) {
            return $image;
        }

        $width = $width ?? $origWidth;
        $height = $height ?? $origHeight;

        $ratio = min($width / $origWidth, $height / $origHeight);
        $newWidth = (int) ($origWidth * $ratio);
        $newHeight = (int) ($origHeight * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        return $resized;
    }

    protected function saveImage(mixed $image, string $path, int $quality): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        match ($ext) {
            'jpg', 'jpeg' => imagejpeg($image, $path, $quality),
            'png' => imagepng($image, $path),
            'gif' => imagegif($image, $path),
            'webp' => imagewebp($image, $path, $quality),
            default => imagejpeg($image, $path, $quality),
        };

        imagedestroy($image);
    }

    protected function deleteConversions(Media $media): void
    {
        $conversions = config('media.image.conversions', []);

        foreach (array_keys($conversions) as $name) {
            $path = $media->getConversionPath($name);

            if (Storage::disk($media->disk)->exists($path)) {
                Storage::disk($media->disk)->delete($path);
            }
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        if (config('media.hash_filenames', true)) {
            return Str::random(40) . '.' . $file->getClientOriginalExtension();
        }

        return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time()
            . '.' . $file->getClientOriginalExtension();
    }

    protected function getImageDimensions(UploadedFile $file): ?array
    {
        $size = getimagesize($file->getRealPath());

        if (!$size) {
            return null;
        }

        return [
            'width' => $size[0],
            'height' => $size[1],
        ];
    }

    public function uploadFromUrl(string $url, array $options = []): Media
    {
        $contents = file_get_contents($url);
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'file-' . time();
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempPath, $contents);
        $file = new UploadedFile($tempPath, $filename);
        return $this->upload($file, $options);
    }

    public function forceDelete(Media $media): bool
    {
        return $this->delete($media);
    }

    public function restore(string $id): ?Media
    {
        $media = Media::withTrashed()->find($id);
        $media?->restore();
        return $media;
    }

    public function bulkMove(array $mediaIds, ?string $folderId): int
    {
        return Media::whereIn('id', $mediaIds)->update(['folder_id' => $folderId]);
    }

    public function createFolder(array $data): \Modules\Media\Domain\Models\MediaFolder
    {
        return \Modules\Media\Domain\Models\MediaFolder::create($data);
    }

    public function renameFolder(string $folderId, string $name): \Modules\Media\Domain\Models\MediaFolder
    {
        $folder = \Modules\Media\Domain\Models\MediaFolder::findOrFail($folderId);
        $folder->update(['name' => $name]);
        return $folder->fresh();
    }

    public function deleteFolder(string $folderId): bool
    {
        return \Modules\Media\Domain\Models\MediaFolder::destroy($folderId) > 0;
    }

    public function regenerateConversions(Media $media): void
    {
        $this->deleteConversions($media);
        $this->generateConversions($media);
    }

    public function crop(Media $media, array $dimensions): Media
    {
        // Implementation for cropping
        return $media;
    }

    public function resize(Media $media, int $width, ?int $height = null): Media
    {
        // Implementation for resizing
        return $media;
    }

    public function optimize(Media $media): Media
    {
        // Implementation for optimization
        return $media;
    }

    public function createVariant(Media $media, string $name, array $transformations): Media
    {
        $clone = $media->replicate();
        $clone->save();
        return $clone;
    }

    public function updateAlt(Media $media, string $alt, ?string $locale = null): Media
    {
        $media->update(['alt_text' => $alt]);
        return $media->fresh();
    }

    public function updateCaption(Media $media, string $caption, ?string $locale = null): Media
    {
        $meta = $media->meta ?? [];
        $meta['caption'] = $caption;
        $media->update(['meta' => $meta]);
        return $media->fresh();
    }

    public function extractMetadata(Media $media): array
    {
        return [
            'filename' => $media->filename,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'dimensions' => $media->dimensions,
        ];
    }

    public function duplicate(Media $media): Media
    {
        $clone = $media->replicate();
        $clone->filename = 'copy-' . $media->filename;
        $clone->save();
        return $clone;
    }

    public function getUsage(Media $media): array
    {
        return ['usages' => []];
    }

    public function replaceFile(Media $media, UploadedFile $file): Media
    {
        Storage::disk($media->disk)->delete($media->path);
        $this->deleteConversions($media);

        $path = dirname($media->path) . '/' . $this->generateFilename($file);
        Storage::disk($media->disk)->put($path, file_get_contents($file->getRealPath()));

        $media->update([
            'path' => $path,
            'filename' => basename($path),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        if ($media->isImage()) {
            dispatch(new GenerateMediaConversions($media));
        }

        return $media->fresh();
    }

    public function download(Media $media): string
    {
        return Storage::disk($media->disk)->path($media->path);
    }
}
