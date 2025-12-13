<?php

declare(strict_types=1);

namespace Modules\Content\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Page Data Transfer Object.
 */
final class PageData extends DataTransferObject
{
    public function __construct(
        public readonly string $status = 'draft',
        public readonly ?string $template = 'default',
        public readonly ?string $featured_image_id = null,
        public readonly ?string $parent_id = null,
        public readonly int $sort_order = 0,
        public readonly bool $show_in_menu = false,
        public readonly array $translations = [],
        public readonly ?array $meta = null,
    ) {}
}
