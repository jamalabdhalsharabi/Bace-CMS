<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

/**
 * Move Media Action.
 *
 * Handles moving media files between folders in the media library.
 *
 * @package Modules\Media\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MoveMediaAction extends Action
{
    /**
     * Create a new MoveMediaAction instance.
     *
     * @param MediaRepository $repository The media repository
     */
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    /**
     * Execute the media move action.
     *
     * @param Media $media The media instance to move
     * @param string|null $folderId The target folder UUID (null for root)
     * 
     * @return Media The moved media instance
     * 
     * @throws \Exception When move fails
     */
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
