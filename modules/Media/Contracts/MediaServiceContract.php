<?php

declare(strict_types=1);

namespace Modules\Media\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Modules\Media\Domain\Models\Media;

/**
 * Interface MediaServiceContract
 * 
 * Defines the contract for media/file management services.
 * Handles uploads, CRUD, folders, image processing, metadata,
 * conversions, and file operations.
 * 
 * @package Modules\Media\Contracts
 */
interface MediaServiceContract
{
    /** @param array $filters @param int $perPage @return LengthAwarePaginator */
    public function list(array $filters = [], int $perPage = 24): LengthAwarePaginator;

    /** @param string $id Media UUID @return Media|null */
    public function find(string $id): ?Media;

    /** @param UploadedFile $file @param array $options @return Media */
    public function upload(UploadedFile $file, array $options = []): Media;

    /** @param array $files @param array $options @return array */
    public function uploadMultiple(array $files, array $options = []): array;

    /** @param string $url @param array $options @return Media */
    public function uploadFromUrl(string $url, array $options = []): Media;

    /** @param Media $media @param array $data @return Media */
    public function update(Media $media, array $data): Media;

    /** @param Media $media @return bool */
    public function delete(Media $media): bool;

    /** @param Media $media @return bool */
    public function forceDelete(Media $media): bool;

    /** @param string $id @return Media|null */
    public function restore(string $id): ?Media;

    /** @param Media $media @param string|null $folderId @return Media */
    public function move(Media $media, ?string $folderId): Media;

    /** @param array $mediaIds @param string|null $folderId @return int */
    public function bulkMove(array $mediaIds, ?string $folderId): int;

    /** @param array $data @return \Modules\Media\Domain\Models\MediaFolder */
    public function createFolder(array $data): \Modules\Media\Domain\Models\MediaFolder;

    /** @param string $folderId @param string $name @return \Modules\Media\Domain\Models\MediaFolder */
    public function renameFolder(string $folderId, string $name): \Modules\Media\Domain\Models\MediaFolder;

    /** @param string $folderId @return bool */
    public function deleteFolder(string $folderId): bool;

    /** @param Media $media @return void */
    public function generateConversions(Media $media): void;

    /** @param Media $media @return void */
    public function regenerateConversions(Media $media): void;

    /** @param Media $media @param array $dimensions @return Media */
    public function crop(Media $media, array $dimensions): Media;

    /** @param Media $media @param int $width @param int|null $height @return Media */
    public function resize(Media $media, int $width, ?int $height = null): Media;

    /** @param Media $media @return Media */
    public function optimize(Media $media): Media;

    /** @param Media $media @param string $name @param array $transformations @return Media */
    public function createVariant(Media $media, string $name, array $transformations): Media;

    /** @param Media $media @param string $alt @param string|null $locale @return Media */
    public function updateAlt(Media $media, string $alt, ?string $locale = null): Media;

    /** @param Media $media @param string $caption @param string|null $locale @return Media */
    public function updateCaption(Media $media, string $caption, ?string $locale = null): Media;

    /** @param Media $media @return array */
    public function extractMetadata(Media $media): array;

    /** @param Media $media @return Media */
    public function duplicate(Media $media): Media;

    /** @param Media $media @return array */
    public function getUsage(Media $media): array;

    /** @param Media $media @param UploadedFile $file @return Media */
    public function replaceFile(Media $media, UploadedFile $file): Media;

    /** @param Media $media @return string */
    public function download(Media $media): string;
}
