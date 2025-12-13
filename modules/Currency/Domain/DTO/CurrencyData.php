<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Currency Data Transfer Object.
 */
final class CurrencyData extends DataTransferObject
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $symbol,
        public readonly int $decimal_places = 2,
        public readonly bool $is_active = true,
        public readonly bool $is_default = false,
        public readonly ?float $exchange_rate = null,
    ) {}
}
