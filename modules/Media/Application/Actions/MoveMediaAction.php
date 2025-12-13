<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

final class MoveMediaAction extends Action
{
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    public function execute(Media $media, ?string $folderId): Media
    {
        $this->repository->update($media->id, ['folder_id' => $folderId]);

        return $media->fresh();
    }

    public function bulkMove(array $mediaIds, ?string $folderId): int
    {
        return Media::whereIn('id', $mediaIds)->update(['folder_id' => $folderId]);
    }
}
