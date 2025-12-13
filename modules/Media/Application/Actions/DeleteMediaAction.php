<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Events\MediaDeleted;
use Modules\Media\Domain\Models\Media;

/**
 * Delete Media Action.
 */
final class DeleteMediaAction extends Action
{
    public function execute(Media $media): bool
    {
        $mediaId = $media->id;
        $path = $media->path;

        Storage::disk($media->disk)->delete($media->path);
        $this->deleteConversions($media);

        $result = $media->forceDelete();

        event(new MediaDeleted($mediaId, $path));

        return $result;
    }

    private function deleteConversions(Media $media): void
    {
        $conversions = config('media.image.conversions', []);

        foreach (array_keys($conversions) as $name) {
            $conversionPath = $media->getConversionPath($name);

            if (Storage::disk($media->disk)->exists($conversionPath)) {
                Storage::disk($media->disk)->delete($conversionPath);
            }
        }
    }
}
