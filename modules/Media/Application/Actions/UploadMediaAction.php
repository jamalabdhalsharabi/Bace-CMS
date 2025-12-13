<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Events\MediaUploaded;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;
use Modules\Media\Jobs\GenerateMediaConversions;

/**
 * Upload Media Action.
 */
final class UploadMediaAction extends Action
{
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    public function execute(UploadedFile $file, array $options = []): Media
    {
        $disk = $options['disk'] ?? config('media.disk', 'public');
        $basePath = $options['path'] ?? config('media.path', 'media');
        $collection = $options['collection'] ?? 'default';

        $filename = $this->generateFilename($file);
        $path = $basePath . '/' . date('Y/m') . '/' . $filename;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $dimensions = $this->getImageDimensions($file);

        $media = $this->repository->create([
            'folder_id' => $options['folder_id'] ?? null,
            'user_id' => $options['user_id'] ?? $this->userId(),
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

        event(new MediaUploaded($media));

        return $media;
    }

    private function generateFilename(UploadedFile $file): string
    {
        if (config('media.hash_filenames', true)) {
            return Str::random(40) . '.' . $file->getClientOriginalExtension();
        }

        return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time()
            . '.' . $file->getClientOriginalExtension();
    }

    private function getImageDimensions(UploadedFile $file): ?array
    {
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            return null;
        }

        $size = getimagesize($file->getRealPath());

        return $size ? ['width' => $size[0], 'height' => $size[1]] : null;
    }
}
