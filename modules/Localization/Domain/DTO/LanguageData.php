<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Language Data Transfer Object.
 */
final class LanguageData extends DataTransferObject
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $native_name = null,
        public readonly string $direction = 'ltr',
        public readonly bool $is_active = true,
        public readonly bool $is_default = false,
        public readonly int $sort_order = 0,
    ) {}
}
