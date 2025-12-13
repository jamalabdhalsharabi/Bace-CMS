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

/**
 * Class MediaService
 *
 * Service class for managing media files including uploads,
 * conversions, folder management, and image processing.
 *
 * @package Modules\Media\Services
 */
class MediaService implements MediaServiceContract
{
    /**
     * Retrieve a paginated list of media files with optional filtering.
     *
     * @param array $filters Optional filters: 'folder_id', 'type', 'search'
     * @param int $perPage Number of results per page (default: 24)
     *
     * @return LengthAwarePaginator Paginated collection of Media models
     */
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

    /**
     * Find a media file by its UUID.
     *
     * @param string $id The UUID of the media to find
     *
     * @return Media|null The found Media or null if not found
     */
    public function find(string $id): ?Media
    {
        return Media::find($id);
    }

    /**
     * Upload a file and create a media record.
     *
     * @param UploadedFile $file The uploaded file to process
     * @param array $options Upload options: 'disk', 'path', 'collection', etc.
     *
     * @return Media The newly created Media record
     */
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

    /**
     * Upload multiple files at once.
     *
     * @param array $files Array of UploadedFile instances
     * @param array $options Shared upload options for all files
     *
     * @return array Array of created Media models
     */
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

    /**
     * Update media metadata.
     *
     * @param Media $media The media to update
     * @param array $data Update data: 'alt_text', 'title', 'meta'
     *
     * @return Media The updated Media with fresh data
     */
    public function update(Media $media, array $data): Media
    {
        $media->update([
            'alt_text' => $data['alt_text'] ?? $media->alt_text,
            'title' => $data['title'] ?? $media->title,
            'meta' => array_merge($media->meta ?? [], $data['meta'] ?? []),
        ]);

        return $media->fresh();
    }

    /**
     * Permanently delete a media file and its conversions.
     *
     * @param Media $media The media to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);

        $this->deleteConversions($media);

        return $media->forceDelete();
    }

    /**
     * Move a media file to a different folder.
     *
     * @param Media $media The media to move
     * @param string|null $folderId Target folder UUID or null for root
     *
     * @return Media The updated Media with new folder
     */
    public function move(Media $media, ?string $folderId): Media
    {
        $media->update(['folder_id' => $folderId]);

        return $media->fresh();
    }

    /**
     * Generate image conversions for a media file.
     *
     * @param Media $media The image media to generate conversions for
     *
     * @return void
     */
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

    /**
     * Create a single image conversion.
     *
     * @param Media $media The source media
     * @param string $name Conversion name
     * @param array $settings Conversion settings (width, height, fit)
     *
     * @return void
     */
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

    /**
     * Load an image resource from file path.
     *
     * @param string $path Absolute path to the image file
     *
     * @return mixed GD image resource or null if unsupported
     */
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

    /**
     * Resize an image to specified dimensions.
     *
     * @param mixed $image GD image resource
     * @param int|null $width Target width or null for auto
     * @param int|null $height Target height or null for auto
     * @param string $fit Fit mode: 'contain', 'cover', etc.
     *
     * @return mixed Resized GD image resource
     */
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

    /**
     * Save an image resource to file.
     *
     * @param mixed $image GD image resource
     * @param string $path Destination file path
     * @param int $quality Output quality (1-100)
     *
     * @return void
     */
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

    /**
     * Delete all conversions for a media file.
     *
     * @param Media $media The media whose conversions to delete
     *
     * @return void
     */
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

    /**
     * Generate a unique filename for uploaded file.
     *
     * @param UploadedFile $file The uploaded file
     *
     * @return string Generated filename with extension
     */
    protected function generateFilename(UploadedFile $file): string
    {
        if (config('media.hash_filenames', true)) {
            return Str::random(40) . '.' . $file->getClientOriginalExtension();
        }

        return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time()
            . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Extract dimensions from an image file.
     *
     * @param UploadedFile $file The image file
     *
     * @return array|null Array with 'width' and 'height' or null
     */
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

    /**
     * Upload a file from a remote URL.
     *
     * @param string $url The URL to download from
     * @param array $options Upload options
     *
     * @return Media The created Media record
     */
    public function uploadFromUrl(string $url, array $options = []): Media
    {
        $contents = file_get_contents($url);
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'file-' . time();
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempPath, $contents);
        $file = new UploadedFile($tempPath, $filename);
        return $this->upload($file, $options);
    }

    /**
     * Force delete a media file permanently.
     *
     * @param Media $media The media to delete
     *
     * @return bool True if successful
     */
    public function forceDelete(Media $media): bool
    {
        return $this->delete($media);
    }

    /**
     * Restore a soft-deleted media file.
     *
     * @param string $id The UUID of the media to restore
     *
     * @return Media|null The restored media or null
     */
    public function restore(string $id): ?Media
    {
        $media = Media::withTrashed()->find($id);
        $media?->restore();
        return $media;
    }

    /**
     * Move multiple media files to a folder.
     *
     * @param array $mediaIds Array of media UUIDs
     * @param string|null $folderId Target folder UUID
     *
     * @return int Number of records updated
     */
    public function bulkMove(array $mediaIds, ?string $folderId): int
    {
        return Media::whereIn('id', $mediaIds)->update(['folder_id' => $folderId]);
    }

    /**
     * Create a new media folder.
     *
     * @param array $data Folder data: 'name', 'parent_id'
     *
     * @return \Modules\Media\Domain\Models\MediaFolder The created folder
     */
    public function createFolder(array $data): \Modules\Media\Domain\Models\MediaFolder
    {
        return \Modules\Media\Domain\Models\MediaFolder::create($data);
    }

    /**
     * Rename an existing folder.
     *
     * @param string $folderId The folder UUID
     * @param string $name The new name
     *
     * @return \Modules\Media\Domain\Models\MediaFolder The renamed folder
     */
    public function renameFolder(string $folderId, string $name): \Modules\Media\Domain\Models\MediaFolder
    {
        $folder = \Modules\Media\Domain\Models\MediaFolder::findOrFail($folderId);
        $folder->update(['name' => $name]);
        return $folder->fresh();
    }

    /**
     * Delete a media folder.
     *
     * @param string $folderId The folder UUID to delete
     *
     * @return bool True if successful
     */
    public function deleteFolder(string $folderId): bool
    {
        return \Modules\Media\Domain\Models\MediaFolder::destroy($folderId) > 0;
    }

    /**
     * Regenerate all conversions for a media file.
     *
     * @param Media $media The media to regenerate conversions for
     *
     * @return void
     */
    public function regenerateConversions(Media $media): void
    {
        $this->deleteConversions($media);
        $this->generateConversions($media);
    }

    /**
     * Crop an image to specified dimensions.
     *
     * @param Media $media The image to crop
     * @param array $dimensions Crop dimensions: 'x', 'y', 'width', 'height'
     *
     * @return Media The cropped media
     */
    public function crop(Media $media, array $dimensions): Media
    {
        // Implementation for cropping
        return $media;
    }

    /**
     * Resize an image to specified dimensions.
     *
     * @param Media $media The image to resize
     * @param int $width Target width
     * @param int|null $height Target height or null for proportional
     *
     * @return Media The resized media
     */
    public function resize(Media $media, int $width, ?int $height = null): Media
    {
        // Implementation for resizing
        return $media;
    }

    /**
     * Optimize a media file for web delivery.
     *
     * @param Media $media The media to optimize
     *
     * @return Media The optimized media
     */
    public function optimize(Media $media): Media
    {
        // Implementation for optimization
        return $media;
    }

    /**
     * Create a variant of the media with transformations.
     *
     * @param Media $media The source media
     * @param string $name Variant name
     * @param array $transformations Array of transformations to apply
     *
     * @return Media The created variant
     */
    public function createVariant(Media $media, string $name, array $transformations): Media
    {
        $clone = $media->replicate();
        $clone->save();
        return $clone;
    }

    /**
     * Update the alt text of a media file.
     *
     * @param Media $media The media to update
     * @param string $alt The new alt text
     * @param string|null $locale Optional locale for translation
     *
     * @return Media The updated media
     */
    public function updateAlt(Media $media, string $alt, ?string $locale = null): Media
    {
        $media->update(['alt_text' => $alt]);
        return $media->fresh();
    }

    /**
     * Update the caption of a media file.
     *
     * @param Media $media The media to update
     * @param string $caption The new caption
     * @param string|null $locale Optional locale for translation
     *
     * @return Media The updated media
     */
    public function updateCaption(Media $media, string $caption, ?string $locale = null): Media
    {
        $meta = $media->meta ?? [];
        $meta['caption'] = $caption;
        $media->update(['meta' => $meta]);
        return $media->fresh();
    }

    /**
     * Extract metadata from a media file.
     *
     * @param Media $media The media to extract metadata from
     *
     * @return array Array of metadata properties
     */
    public function extractMetadata(Media $media): array
    {
        return [
            'filename' => $media->filename,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'dimensions' => $media->dimensions,
        ];
    }

    /**
     * Duplicate a media file.
     *
     * @param Media $media The media to duplicate
     *
     * @return Media The duplicated media
     */
    public function duplicate(Media $media): Media
    {
        $clone = $media->replicate();
        $clone->filename = 'copy-' . $media->filename;
        $clone->save();
        return $clone;
    }

    /**
     * Get usage information for a media file.
     *
     * @param Media $media The media to check
     *
     * @return array Array of usage locations
     */
    public function getUsage(Media $media): array
    {
        return ['usages' => []];
    }

    /**
     * Replace the file content of a media record.
     *
     * @param Media $media The media to replace
     * @param UploadedFile $file The new file
     *
     * @return Media The updated media
     */
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

    /**
     * Get the download path for a media file.
     *
     * @param Media $media The media to download
     *
     * @return string Absolute path to the file
     */
    public function download(Media $media): string
    {
        return Storage::disk($media->disk)->path($media->path);
    }
}
