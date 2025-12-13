<?php

declare(strict_types=1);

namespace Modules\Products\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Product Data Transfer Object.
 *
 * Immutable data structure for transferring product data between layers.
 */
final class ProductData extends DataTransferObject
{
    /**
     * @param string $sku Product SKU
     * @param string|null $barcode Product barcode
     * @param string $type Product type (physical, digital, virtual, subscription)
     * @param string $status Publication status
     * @param string $visibility Product visibility
     * @param bool $is_featured Whether product is featured
     * @param bool $track_inventory Whether to track inventory
     * @param bool $allow_backorder Whether to allow backorders
     * @param bool $requires_shipping Whether shipping is required
     * @param float|null $weight Product weight
     * @param string|null $weight_unit Weight unit
     * @param string|null $tax_class Tax class
     * @param bool $has_variants Whether product has variants
     * @param array<string, mixed> $translations Translations keyed by locale
     * @param array<string, mixed>|null $dimensions Product dimensions
     * @param array<string, mixed>|null $meta Additional metadata
     */
    public function __construct(
        public readonly string $sku,
        public readonly ?string $barcode = null,
        public readonly string $type = 'physical',
        public readonly string $status = 'draft',
        public readonly string $visibility = 'visible',
        public readonly bool $is_featured = false,
        public readonly bool $track_inventory = true,
        public readonly bool $allow_backorder = false,
        public readonly bool $requires_shipping = true,
        public readonly ?float $weight = null,
        public readonly ?string $weight_unit = 'kg',
        public readonly ?string $tax_class = null,
        public readonly bool $has_variants = false,
        public readonly array $translations = [],
        public readonly ?array $dimensions = null,
        public readonly ?array $meta = null,
    ) {}
}
