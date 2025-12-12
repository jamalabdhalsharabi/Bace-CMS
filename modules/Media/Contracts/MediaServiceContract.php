<?php

declare(strict_types=1);

namespace Modules\Media\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Modules\Media\Domain\Models\Media;

interface MediaServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 24): LengthAwarePaginator;
    public function find(string $id): ?Media;
    public function upload(UploadedFile $file, array $options = []): Media;
    public function uploadMultiple(array $files, array $options = []): array;
    public function uploadFromUrl(string $url, array $options = []): Media;
    public function update(Media $media, array $data): Media;
    public function delete(Media $media): bool;
    public function forceDelete(Media $media): bool;
    public function restore(string $id): ?Media;

    // Organization
    public function move(Media $media, ?string $folderId): Media;
    public function bulkMove(array $mediaIds, ?string $folderId): int;
    public function createFolder(array $data): \Modules\Media\Domain\Models\MediaFolder;
    public function renameFolder(string $folderId, string $name): \Modules\Media\Domain\Models\MediaFolder;
    public function deleteFolder(string $folderId): bool;

    // Processing
    public function generateConversions(Media $media): void;
    public function regenerateConversions(Media $media): void;
    public function crop(Media $media, array $dimensions): Media;
    public function resize(Media $media, int $width, ?int $height = null): Media;
    public function optimize(Media $media): Media;
    public function createVariant(Media $media, string $name, array $transformations): Media;

    // Metadata
    public function updateAlt(Media $media, string $alt, ?string $locale = null): Media;
    public function updateCaption(Media $media, string $caption, ?string $locale = null): Media;
    public function extractMetadata(Media $media): array;

    // Other
    public function duplicate(Media $media): Media;
    public function getUsage(Media $media): array;
    public function replaceFile(Media $media, UploadedFile $file): Media;
    public function download(Media $media): string;
}
