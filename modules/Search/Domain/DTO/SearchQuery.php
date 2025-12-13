<?php

declare(strict_types=1);

namespace Modules\Search\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Search Query Data Transfer Object.
 */
final class SearchQuery extends DataTransferObject
{
    public function __construct(
        public readonly string $query,
        public readonly array $types = [],
        public readonly ?string $locale = null,
        public readonly int $limit = 20,
        public readonly int $page = 1,
        public readonly array $filters = [],
    ) {}
}
