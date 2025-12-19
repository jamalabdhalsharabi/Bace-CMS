<?php

declare(strict_types=1);

namespace Modules\Media\Application\Services;

use Illuminate\Http\UploadedFile;
use Modules\Media\Application\Actions\CreateFolderAction;
use Modules\Media\Application\Actions\DeleteFolderAction;
use Modules\Media\Application\Actions\DeleteMediaAction;
use Modules\Media\Application\Actions\DuplicateMediaAction;
use Modules\Media\Application\Actions\MoveMediaAction;
use Modules\Media\Application\Actions\UpdateMediaAction;
use Modules\Media\Application\Actions\UploadMediaAction;
use Modules\Media\Domain\DTO\MediaUploadData;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Models\MediaFolder;

/**
 * Media Command Service.
 *
 * Orchestrates all write operations for media via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Media\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MediaCommandService
{
    /**
     * Create a new MediaCommandService instance.
     *
     * @param UploadMediaAction $uploadAction Action for uploading media
     * @param DeleteMediaAction $deleteAction Action for deleting media
     * @param UpdateMediaAction $updateAction Action for updating media
     * @param MoveMediaAction $moveAction Action for moving media
     * @param DuplicateMediaAction $duplicateAction Action for duplicating media
     * @param CreateFolderAction $createFolderAction Action for creating folders
     * @param DeleteFolderAction $deleteFolderAction Action for deleting folders
     */
    public function __construct(
        private readonly UploadMediaAction $uploadAction,
        private readonly DeleteMediaAction $deleteAction,
        private readonly UpdateMediaAction $updateAction,
        private readonly MoveMediaAction $moveAction,
        private readonly DuplicateMediaAction $duplicateAction,
        private readonly CreateFolderAction $createFolderAction,
        private readonly DeleteFolderAction $deleteFolderAction,
    ) {}

    public function upload(UploadedFile $file, ?string $folderId = null, array $meta = []): Media
    {
        $data = new MediaUploadData(
            file: $file,
            folder_id: $folderId,
            alt_text: $meta['alt_text'] ?? null,
            title: $meta['title'] ?? null,
        );

        return $this->uploadAction->execute($data);
    }

    public function update(Media $media, array $data): Media
    {
        return $this->updateAction->execute($media, $data);
    }

    public function delete(Media $media): bool
    {
        return $this->deleteAction->execute($media);
    }

    public function move(Media $media, ?string $folderId): Media
    {
        return $this->moveAction->execute($media, $folderId);
    }

    public function bulkMove(array $mediaIds, ?string $folderId): int
    {
        return $this->moveAction->bulkMove($mediaIds, $folderId);
    }

    public function duplicate(Media $media): Media
    {
        return $this->duplicateAction->execute($media);
    }

    public function createFolder(array $data): MediaFolder
    {
        return $this->createFolderAction->execute($data);
    }

    public function deleteFolder(string $folderId): bool
    {
        return $this->deleteFolderAction->execute($folderId);
    }
}
