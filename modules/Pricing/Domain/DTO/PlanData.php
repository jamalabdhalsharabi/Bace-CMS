<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Plan Data Transfer Object.
 */
final class PlanData extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly float $price,
        public readonly string $billing_period = 'monthly',
        public readonly ?string $description = null,
        public readonly array $features = [],
        public readonly bool $is_active = true,
        public readonly bool $is_featured = false,
        public readonly int $sort_order = 0,
        public readonly ?int $trial_days = null,
        public readonly ?array $meta = null,
    ) {}
}
