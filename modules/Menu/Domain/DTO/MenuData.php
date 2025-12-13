<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Menu Data Transfer Object.
 */
final class MenuData extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $location,
        public readonly bool $is_active = true,
        public readonly array $items = [],
    ) {}
}
