<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Setting Data Transfer Object.
 */
final class SettingData extends DataTransferObject
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly string $group = 'general',
        public readonly ?string $type = null,
        public readonly bool $is_public = false,
    ) {}
}
