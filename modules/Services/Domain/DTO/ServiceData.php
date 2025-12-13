<?php

declare(strict_types=1);

namespace Modules\Services\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Service Data Transfer Object.
 */
final class ServiceData extends DataTransferObject
{
    public function __construct(
        public readonly string $status = 'draft',
        public readonly bool $is_featured = false,
        public readonly ?string $featured_image_id = null,
        public readonly ?string $icon = null,
        public readonly int $sort_order = 0,
        public readonly array $translations = [],
        public readonly ?array $category_ids = null,
        public readonly ?array $meta = null,
    ) {}
}
