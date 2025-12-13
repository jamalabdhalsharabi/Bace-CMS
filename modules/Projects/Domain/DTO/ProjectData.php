<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Project Data Transfer Object.
 */
final class ProjectData extends DataTransferObject
{
    public function __construct(
        public readonly string $status = 'draft',
        public readonly bool $is_featured = false,
        public readonly ?string $client_name = null,
        public readonly ?string $project_date = null,
        public readonly ?string $project_url = null,
        public readonly ?string $featured_image_id = null,
        public readonly array $translations = [],
        public readonly ?array $category_ids = null,
        public readonly ?array $gallery_ids = null,
        public readonly ?array $meta = null,
    ) {}
}
