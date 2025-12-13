<?php

declare(strict_types=1);

namespace Modules\Media\Domain\DTO;

use Illuminate\Http\UploadedFile;
use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Media Upload Data Transfer Object.
 */
final class MediaUploadData extends DataTransferObject
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ?string $folder_id = null,
        public readonly ?string $collection = 'default',
        public readonly ?string $disk = null,
        public readonly ?string $path = null,
        public readonly ?string $alt_text = null,
        public readonly ?string $title = null,
    ) {}
}
