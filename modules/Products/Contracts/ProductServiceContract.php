<?php

declare(strict_types=1);

namespace Modules\Products\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Products\Domain\Models\Product;

/**
 * Interface ProductServiceContract
 * 
 * Defines the contract for product management services.
 * Handles CRUD, workflow, variants, pricing, inventory,
 * relations, cloning, bulk operations, and import/export.
 * 
 * @package Modules\Products\Contracts
 */
interface ProductServiceContract
{
    /**
     * Get paginated list of products with optional filters.
     *
     * @param array $filters Filter criteria (status, category, price_range, etc.)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /** @param string $id Product UUID @return Product|null */
    public function find(string $id): ?Product;

    /** @param string $slug Product slug @return Product|null */
    public function findBySlug(string $slug): ?Product;

    /** @param string $sku Product SKU @return Product|null */
    public function findBySku(string $sku): ?Product;

    /** @param array $data Product data @return Product */
    public function create(array $data): Product;

    /** @param Product $product @param array $data @return Product */
    public function update(Product $product, array $data): Product;

    /** @param Product $product @return bool */
    public function delete(Product $product): bool;

    /** @param Product $product @return bool */
    public function forceDelete(Product $product): bool;

    /** @param string $id @return Product|null */
    public function restore(string $id): ?Product;

    /** @param Product $product @param array $data @return Product */
    public function saveDraft(Product $product, array $data): Product;

    /** @param Product $product @return Product */
    public function submitForReview(Product $product): Product;

    /** @param Product $product @return Product */
    public function approve(Product $product): Product;

    /** @param Product $product @param string|null $reason @return Product */
    public function reject(Product $product, ?string $reason = null): Product;

    /** @param Product $product @return Product */
    public function publish(Product $product): Product;

    /** @param Product $product @param \DateTime $date @return Product */
    public function schedule(Product $product, \DateTime $date): Product;

    /** @param Product $product @return Product */
    public function unpublish(Product $product): Product;

    /** @param Product $product @return Product */
    public function archive(Product $product): Product;

    /** @param Product $product @return Product */
    public function unarchive(Product $product): Product;

    /** @param Product $product @return Product */
    public function discontinue(Product $product): Product;

    /** @param Product $product @param array $data @return \Modules\Products\Domain\Models\ProductVariant */
    public function addVariant(Product $product, array $data): \Modules\Products\Domain\Models\ProductVariant;

    /** @param Product $product @param string $variantId @param array $data @return \Modules\Products\Domain\Models\ProductVariant */
    public function updateVariant(Product $product, string $variantId, array $data): \Modules\Products\Domain\Models\ProductVariant;

    /** @param Product $product @param string $variantId @return bool */
    public function deleteVariant(Product $product, string $variantId): bool;

    /** @param Product $product @param string $variantId @param bool $active @return \Modules\Products\Domain\Models\ProductVariant */
    public function toggleVariant(Product $product, string $variantId, bool $active): \Modules\Products\Domain\Models\ProductVariant;

    /** @param Product $product @param string $currencyId @param float $amount @param float|null $compareAt @return Product */
    public function setPrice(Product $product, string $currencyId, float $amount, ?float $compareAt = null): Product;

    /** @param Product $product @param string $currencyId @param float $amount @return Product */
    public function updatePrice(Product $product, string $currencyId, float $amount): Product;

    /** @param Product $product @param string $currencyId @param float $amount @param \DateTime $startAt @param \DateTime|null $endAt @return Product */
    public function schedulePrice(Product $product, string $currencyId, float $amount, \DateTime $startAt, ?\DateTime $endAt = null): Product;

    /** @param Product $product @param float $percentage @param \DateTime|null $until @return Product */
    public function applyDiscount(Product $product, float $percentage, ?\DateTime $until = null): Product;

    /** @param Product $product @return Product */
    public function removeDiscount(Product $product): Product;

    /** @param Product $product @param int $quantity @param string $type @param string|null $reason @return Product */
    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product;

    /** @param Product $product @param int $quantity @param string $orderId @return bool */
    public function reserveStock(Product $product, int $quantity, string $orderId): bool;

    /** @param Product $product @param string $orderId @return bool */
    public function releaseReservation(Product $product, string $orderId): bool;

    /** @param Product $product @param string $orderId @return bool */
    public function confirmReservation(Product $product, string $orderId): bool;

    /** @param Product $product @param int $threshold @return Product */
    public function setLowStockThreshold(Product $product, int $threshold): Product;

    /** @param Product $product @return Product */
    public function enablePreorder(Product $product): Product;

    /** @param Product $product @param array $categoryIds @return Product */
    public function syncCategories(Product $product, array $categoryIds): Product;

    /** @param Product $product @param array $productIds @return Product */
    public function attachRelated(Product $product, array $productIds): Product;

    /** @param Product $product @param array $mediaIds @return Product */
    public function attachMedia(Product $product, array $mediaIds): Product;

    /** @param Product $product @return Product */
    public function duplicate(Product $product): Product;

    /** @param array $productIds @param float $percentage @param string $operation @return int */
    public function bulkUpdatePrices(array $productIds, float $percentage, string $operation): int;

    /** @param array $data @return int */
    public function bulkUpdateStock(array $data): int;

    /** @param array $data @return array */
    public function import(array $data): array;

    /** @param array $filters @return array */
    public function export(array $filters = []): array;

    /** @param Product $product @param string $channel @return bool */
    public function syncExternal(Product $product, string $channel): bool;
}
