<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Taxonomy Data Transfer Object.
 */
final class TaxonomyData extends DataTransferObject
{
    public function __construct(
        public readonly string $type_id,
        public readonly ?string $parent_id = null,
        public readonly int $sort_order = 0,
        public readonly array $translations = [],
        public readonly ?array $meta = null,
    ) {}
}
