<?php

declare(strict_types=1);

namespace Modules\Products\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Products\Domain\Models\Product;

interface ProductServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function findBySku(string $sku): ?Product;
    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;
    public function delete(Product $product): bool;
    public function forceDelete(Product $product): bool;
    public function restore(string $id): ?Product;

    // Workflow
    public function saveDraft(Product $product, array $data): Product;
    public function submitForReview(Product $product): Product;
    public function approve(Product $product): Product;
    public function reject(Product $product, ?string $reason = null): Product;
    public function publish(Product $product): Product;
    public function schedule(Product $product, \DateTime $date): Product;
    public function unpublish(Product $product): Product;
    public function archive(Product $product): Product;
    public function unarchive(Product $product): Product;
    public function discontinue(Product $product): Product;

    // Variants
    public function addVariant(Product $product, array $data): \Modules\Products\Domain\Models\ProductVariant;
    public function updateVariant(Product $product, string $variantId, array $data): \Modules\Products\Domain\Models\ProductVariant;
    public function deleteVariant(Product $product, string $variantId): bool;
    public function toggleVariant(Product $product, string $variantId, bool $active): \Modules\Products\Domain\Models\ProductVariant;

    // Pricing
    public function setPrice(Product $product, string $currencyId, float $amount, ?float $compareAt = null): Product;
    public function updatePrice(Product $product, string $currencyId, float $amount): Product;
    public function schedulePrice(Product $product, string $currencyId, float $amount, \DateTime $startAt, ?\DateTime $endAt = null): Product;
    public function applyDiscount(Product $product, float $percentage, ?\DateTime $until = null): Product;
    public function removeDiscount(Product $product): Product;

    // Inventory
    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product;
    public function reserveStock(Product $product, int $quantity, string $orderId): bool;
    public function releaseReservation(Product $product, string $orderId): bool;
    public function confirmReservation(Product $product, string $orderId): bool;
    public function setLowStockThreshold(Product $product, int $threshold): Product;
    public function enablePreorder(Product $product): Product;

    // Relations
    public function syncCategories(Product $product, array $categoryIds): Product;
    public function attachRelated(Product $product, array $productIds): Product;
    public function attachMedia(Product $product, array $mediaIds): Product;

    // Clone & Bulk
    public function duplicate(Product $product): Product;
    public function bulkUpdatePrices(array $productIds, float $percentage, string $operation): int;
    public function bulkUpdateStock(array $data): int;

    // Import/Export
    public function import(array $data): array;
    public function export(array $filters = []): array;
    public function syncExternal(Product $product, string $channel): bool;
}
