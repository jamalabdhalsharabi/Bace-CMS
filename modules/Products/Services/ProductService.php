<?php

declare(strict_types=1);

namespace Modules\Products\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\Domain\Models\Product;

/**
 * Class ProductService
 *
 * Service class for managing products including CRUD operations,
 * workflow, pricing, inventory, variants, and stock management.
 *
 * @package Modules\Products\Services
 */
class ProductService implements ProductServiceContract
{
    /**
     * Retrieve a paginated list of products with optional filtering.
     *
     * @param array $filters Optional filters: 'status', 'type', 'featured', 'stock_status', 'search'
     * @param int $perPage Number of results per page (default: 20)
     *
     * @return LengthAwarePaginator Paginated collection of Product models
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::with(['translation', 'prices', 'inventory']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        if (!empty($filters['stock_status'])) {
            $query->where('stock_status', $filters['stock_status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('translations', fn($t) => 
                      $t->where('name', 'LIKE', "%{$search}%")
                  );
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a product by its UUID.
     *
     * @param string $id The UUID of the product to find
     *
     * @return Product|null The found Product or null if not found
     */
    public function find(string $id): ?Product
    {
        return Product::with(['translations', 'variants.prices', 'prices', 'inventory'])->find($id);
    }

    /**
     * Find a product by its URL slug.
     *
     * @param string $slug The URL slug to search for
     *
     * @return Product|null The found Product or null if not found
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::findBySlug($slug)?->load(['translations', 'variants', 'prices']);
    }

    /**
     * Find a product by its SKU code.
     *
     * @param string $sku The SKU to search for
     *
     * @return Product|null The found Product or null if not found
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create a new product with translations and pricing.
     *
     * @param array $data Product data including translations and prices
     *
     * @return Product The newly created Product
     *
     * @throws \Throwable If the transaction fails
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'type' => $data['type'] ?? 'physical',
                'status' => $data['status'] ?? 'draft',
                'visibility' => $data['visibility'] ?? 'visible',
                'is_featured' => $data['is_featured'] ?? false,
                'track_inventory' => $data['track_inventory'] ?? true,
                'allow_backorder' => $data['allow_backorder'] ?? false,
                'stock_status' => $data['stock_status'] ?? 'in_stock',
                'requires_shipping' => $data['requires_shipping'] ?? true,
                'weight' => $data['weight'] ?? null,
                'weight_unit' => $data['weight_unit'] ?? 'kg',
                'tax_class' => $data['tax_class'] ?? null,
                'has_variants' => !empty($data['variants']),
                'meta' => $data['meta'] ?? null,
                'dimensions' => $data['dimensions'] ?? null,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $product->translations()->create([
                        'locale' => $locale,
                        'name' => $trans['name'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                        'short_description' => $trans['short_description'] ?? null,
                        'description' => $trans['description'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]);
                }
            }

            if (!empty($data['prices'])) {
                foreach ($data['prices'] as $price) {
                    $product->prices()->create($price);
                }
            }

            if ($product->track_inventory) {
                $product->inventory()->create([
                    'quantity' => $data['quantity'] ?? 0,
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
                ]);
            }

            return $product->fresh(['translations', 'prices', 'inventory']);
        });
    }

    /**
     * Update an existing product and its translations.
     *
     * @param Product $product The product to update
     * @param array $data Updated data including optional translations
     *
     * @return Product The updated Product
     *
     * @throws \Throwable If the transaction fails
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update(array_filter([
                'barcode' => $data['barcode'] ?? $product->barcode,
                'type' => $data['type'] ?? $product->type,
                'visibility' => $data['visibility'] ?? $product->visibility,
                'is_featured' => $data['is_featured'] ?? $product->is_featured,
                'track_inventory' => $data['track_inventory'] ?? $product->track_inventory,
                'allow_backorder' => $data['allow_backorder'] ?? $product->allow_backorder,
                'requires_shipping' => $data['requires_shipping'] ?? $product->requires_shipping,
                'weight' => $data['weight'] ?? $product->weight,
                'tax_class' => $data['tax_class'] ?? $product->tax_class,
                'meta' => $data['meta'] ?? $product->meta,
                'dimensions' => $data['dimensions'] ?? $product->dimensions,
            ], fn($v) => $v !== null));

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $product->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'name' => $trans['name'],
                            'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                            'short_description' => $trans['short_description'] ?? null,
                            'description' => $trans['description'] ?? null,
                            'meta_title' => $trans['meta_title'] ?? null,
                            'meta_description' => $trans['meta_description'] ?? null,
                        ]
                    );
                }
            }

            return $product->fresh(['translations', 'prices', 'inventory']);
        });
    }

    /**
     * Soft-delete a product.
     *
     * @param Product $product The product to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Publish a product immediately.
     *
     * @param Product $product The product to publish
     *
     * @return Product The published product
     */
    public function publish(Product $product): Product
    {
        $product->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        return $product->fresh();
    }

    /**
     * Unpublish a product and revert to draft status.
     *
     * @param Product $product The product to unpublish
     *
     * @return Product The unpublished product
     */
    public function unpublish(Product $product): Product
    {
        $product->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
        return $product->fresh();
    }

    /**
     * Update product stock quantity.
     *
     * @param Product $product The product to update stock for
     * @param int $quantity The quantity to adjust
     * @param string $type Adjustment type: 'add', 'subtract', 'set'
     * @param string|null $reason Optional reason for the adjustment
     *
     * @return Product The product with updated inventory
     */
    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product
    {
        if ($product->inventory) {
            $product->inventory->adjustStock($quantity, $type, $reason);
            $this->updateStockStatus($product);
        }
        return $product->fresh(['inventory']);
    }

    /**
     * Update the stock status based on inventory levels.
     *
     * @param Product $product The product to update status for
     *
     * @return void
     */
    protected function updateStockStatus(Product $product): void
    {
        $inventory = $product->inventory;
        if (!$inventory) return;

        $status = match (true) {
            $inventory->isOutOfStock() && $product->allow_backorder => 'on_backorder',
            $inventory->isOutOfStock() => 'out_of_stock',
            default => 'in_stock',
        };

        $product->update(['stock_status' => $status]);
    }

    /**
     * Permanently delete a product from the database.
     *
     * @param Product $product The product to delete
     *
     * @return bool True if successful
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Restore a soft-deleted product.
     *
     * @param string $id The UUID of the product to restore
     *
     * @return Product|null The restored product or null
     */
    public function restore(string $id): ?Product
    {
        $product = Product::withTrashed()->find($id);
        $product?->restore();
        return $product;
    }

    /**
     * Save product changes as a draft.
     *
     * @param Product $product The product to save
     * @param array $data The data to update
     *
     * @return Product The updated draft product
     */
    public function saveDraft(Product $product, array $data): Product
    {
        $data['status'] = 'draft';
        return $this->update($product, $data);
    }

    /**
     * Submit a product for review.
     *
     * @param Product $product The product to submit
     *
     * @return Product The submitted product
     */
    public function submitForReview(Product $product): Product
    {
        $product->update(['status' => 'pending_review']);
        return $product->fresh();
    }

    /**
     * Approve a product after review.
     *
     * @param Product $product The product to approve
     *
     * @return Product The approved product
     */
    public function approve(Product $product): Product
    {
        $product->update(['status' => 'approved']);
        return $product->fresh();
    }

    /**
     * Reject a product during review.
     *
     * @param Product $product The product to reject
     * @param string|null $reason Rejection reason
     *
     * @return Product The rejected product
     */
    public function reject(Product $product, ?string $reason = null): Product
    {
        $product->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $product->fresh();
    }

    /**
     * Schedule a product for future publication.
     *
     * @param Product $product The product to schedule
     * @param \DateTime $date The publication date
     *
     * @return Product The scheduled product
     */
    public function schedule(Product $product, \DateTime $date): Product
    {
        $product->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $product->fresh();
    }

    /**
     * Archive a product.
     *
     * @param Product $product The product to archive
     *
     * @return Product The archived product
     */
    public function archive(Product $product): Product
    {
        $product->update(['status' => 'archived', 'archived_at' => now()]);
        return $product->fresh();
    }

    /**
     * Restore an archived product to draft.
     *
     * @param Product $product The product to unarchive
     *
     * @return Product The unarchived product
     */
    public function unarchive(Product $product): Product
    {
        $product->update(['status' => 'draft', 'archived_at' => null]);
        return $product->fresh();
    }

    /**
     * Mark a product as discontinued.
     *
     * @param Product $product The product to discontinue
     *
     * @return Product The discontinued product
     */
    public function discontinue(Product $product): Product
    {
        $product->update(['status' => 'discontinued']);
        return $product->fresh();
    }

    /**
     * Add a variant to a product.
     *
     * @param Product $product The parent product
     * @param array $data Variant data
     *
     * @return \Modules\Products\Domain\Models\ProductVariant The created variant
     */
    public function addVariant(Product $product, array $data): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->create($data);
        $product->update(['has_variants' => true]);
        return $variant;
    }

    /**
     * Update a product variant.
     *
     * @param Product $product The parent product
     * @param string $variantId The variant UUID
     * @param array $data Updated variant data
     *
     * @return \Modules\Products\Domain\Models\ProductVariant The updated variant
     */
    public function updateVariant(Product $product, string $variantId, array $data): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->findOrFail($variantId);
        $variant->update($data);
        return $variant->fresh();
    }

    /**
     * Delete a product variant.
     *
     * @param Product $product The parent product
     * @param string $variantId The variant UUID to delete
     *
     * @return bool True if successful
     */
    public function deleteVariant(Product $product, string $variantId): bool
    {
        return $product->variants()->where('id', $variantId)->delete() > 0;
    }

    /**
     * Toggle variant active status.
     *
     * @param Product $product The parent product
     * @param string $variantId The variant UUID
     * @param bool $active The new active status
     *
     * @return \Modules\Products\Domain\Models\ProductVariant The updated variant
     */
    public function toggleVariant(Product $product, string $variantId, bool $active): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->findOrFail($variantId);
        $variant->update(['is_active' => $active]);
        return $variant->fresh();
    }

    /**
     * Set or update a product price for a currency.
     *
     * @param Product $product The product
     * @param string $currencyId The currency UUID
     * @param float $amount The price amount
     * @param float|null $compareAt Optional compare-at price
     *
     * @return Product The product with updated prices
     */
    public function setPrice(Product $product, string $currencyId, float $amount, ?float $compareAt = null): Product
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            ['amount' => $amount, 'compare_at_price' => $compareAt]
        );
        return $product->fresh(['prices']);
    }

    /**
     * Update existing product price.
     *
     * @param Product $product The product
     * @param string $currencyId The currency UUID
     * @param float $amount The new price amount
     *
     * @return Product The product with updated prices
     */
    public function updatePrice(Product $product, string $currencyId, float $amount): Product
    {
        $product->prices()->where('currency_id', $currencyId)->update(['amount' => $amount]);
        return $product->fresh(['prices']);
    }

    /**
     * Schedule a future price change.
     *
     * @param Product $product The product
     * @param string $currencyId The currency UUID
     * @param float $amount The scheduled price
     * @param \DateTime $startAt Start date for the price
     * @param \DateTime|null $endAt Optional end date
     *
     * @return Product The product with scheduled price
     */
    public function schedulePrice(Product $product, string $currencyId, float $amount, \DateTime $startAt, ?\DateTime $endAt = null): Product
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            ['scheduled_amount' => $amount, 'price_starts_at' => $startAt, 'price_ends_at' => $endAt]
        );
        return $product->fresh(['prices']);
    }

    /**
     * Apply a percentage discount to all prices.
     *
     * @param Product $product The product
     * @param float $percentage Discount percentage (0-100)
     * @param \DateTime|null $until Optional discount end date
     *
     * @return Product The product with discounted prices
     */
    public function applyDiscount(Product $product, float $percentage, ?\DateTime $until = null): Product
    {
        foreach ($product->prices as $price) {
            $discountedAmount = $price->amount * (1 - $percentage / 100);
            $price->update([
                'compare_at_price' => $price->amount,
                'amount' => $discountedAmount,
                'discount_ends_at' => $until,
            ]);
        }
        return $product->fresh(['prices']);
    }

    /**
     * Remove all discounts from a product.
     *
     * @param Product $product The product
     *
     * @return Product The product with original prices
     */
    public function removeDiscount(Product $product): Product
    {
        foreach ($product->prices as $price) {
            if ($price->compare_at_price) {
                $price->update([
                    'amount' => $price->compare_at_price,
                    'compare_at_price' => null,
                    'discount_ends_at' => null,
                ]);
            }
        }
        return $product->fresh(['prices']);
    }

    /**
     * Reserve stock for an order.
     *
     * @param Product $product The product
     * @param int $quantity Quantity to reserve
     * @param string $orderId The order UUID
     *
     * @return bool True if reservation successful
     */
    public function reserveStock(Product $product, int $quantity, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->reserve($quantity, $orderId);
    }

    /**
     * Release a stock reservation.
     *
     * @param Product $product The product
     * @param string $orderId The order UUID
     *
     * @return bool True if successful
     */
    public function releaseReservation(Product $product, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->releaseReservation($orderId);
    }

    /**
     * Confirm a stock reservation.
     *
     * @param Product $product The product
     * @param string $orderId The order UUID
     *
     * @return bool True if successful
     */
    public function confirmReservation(Product $product, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->confirmReservation($orderId);
    }

    /**
     * Set low stock threshold.
     *
     * @param Product $product The product
     * @param int $threshold The threshold quantity
     *
     * @return Product The updated product
     */
    public function setLowStockThreshold(Product $product, int $threshold): Product
    {
        $product->inventory?->update(['low_stock_threshold' => $threshold]);
        return $product->fresh(['inventory']);
    }

    /**
     * Enable preorder for a product.
     *
     * @param Product $product The product
     *
     * @return Product The updated product
     */
    public function enablePreorder(Product $product): Product
    {
        $product->update(['allow_backorder' => true, 'is_preorder' => true]);
        return $product->fresh();
    }

    /**
     * Sync product categories.
     *
     * @param Product $product The product
     * @param array $categoryIds Category UUIDs
     *
     * @return Product The updated product
     */
    public function syncCategories(Product $product, array $categoryIds): Product
    {
        $product->categories()->sync($categoryIds);
        return $product->fresh(['categories']);
    }

    /**
     * Attach related products.
     *
     * @param Product $product The product
     * @param array $productIds Related product UUIDs
     *
     * @return Product The updated product
     */
    public function attachRelated(Product $product, array $productIds): Product
    {
        $product->relatedProducts()->sync($productIds);
        return $product->fresh(['relatedProducts']);
    }

    /**
     * Attach media to a product.
     *
     * @param Product $product The product
     * @param array $mediaIds Media UUIDs
     *
     * @return Product The updated product
     */
    public function attachMedia(Product $product, array $mediaIds): Product
    {
        foreach ($mediaIds as $index => $mediaId) {
            $product->media()->updateOrCreate(
                ['media_id' => $mediaId],
                ['sort_order' => $index]
            );
        }
        return $product->fresh(['media']);
    }

    /**
     * Duplicate a product.
     *
     * @param Product $product The product to duplicate
     *
     * @return Product The duplicated product
     */
    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $clone = $product->replicate(['sku', 'status', 'published_at']);
            $clone->sku = $product->sku . '-copy-' . time();
            $clone->status = 'draft';
            $clone->save();

            foreach ($product->translations as $trans) {
                $clone->translations()->create($trans->only(['locale', 'name', 'short_description', 'description']));
            }

            foreach ($product->prices as $price) {
                $clone->prices()->create($price->only(['currency_id', 'amount', 'compare_at_price']));
            }

            return $clone->fresh(['translations', 'prices']);
        });
    }

    /**
     * Bulk update prices for multiple products.
     *
     * @param array $productIds Array of product UUIDs
     * @param float $percentage Percentage to adjust
     * @param string $operation 'increase' or 'decrease'
     *
     * @return int Number of products updated
     */
    public function bulkUpdatePrices(array $productIds, float $percentage, string $operation): int
    {
        $count = 0;
        foreach ($productIds as $id) {
            $product = $this->find($id);
            if (!$product) continue;

            foreach ($product->prices as $price) {
                $newAmount = $operation === 'increase' 
                    ? $price->amount * (1 + $percentage / 100)
                    : $price->amount * (1 - $percentage / 100);
                $price->update(['amount' => $newAmount]);
            }
            $count++;
        }
        return $count;
    }

    /**
     * Bulk update stock for multiple products.
     *
     * @param array $data Array of stock updates
     *
     * @return int Number of products updated
     */
    public function bulkUpdateStock(array $data): int
    {
        $count = 0;
        foreach ($data as $item) {
            $product = $this->find($item['id']);
            if (!$product) continue;
            $this->updateStock($product, $item['quantity'], $item['type'] ?? 'set', $item['reason'] ?? null);
            $count++;
        }
        return $count;
    }

    /**
     * Import products from array data.
     *
     * @param array $data Array of product data
     *
     * @return array Import results with counts
     */
    public function import(array $data): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $existing = $this->findBySku($item['sku'] ?? '');
                if ($existing) {
                    $this->update($existing, $item);
                    $results['updated']++;
                } else {
                    $this->create($item);
                    $results['created']++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Export products to array.
     *
     * @param array $filters Optional filters
     *
     * @return array Array of product data
     */
    public function export(array $filters = []): array
    {
        return Product::with(['translations', 'prices', 'inventory'])
            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->get()
            ->map(fn($p) => $p->toArray())
            ->toArray();
    }

    /**
     * Sync product with external channel.
     *
     * @param Product $product The product
     * @param string $channel External channel name
     *
     * @return bool True if successful
     */
    public function syncExternal(Product $product, string $channel): bool
    {
        // Implementation depends on external channel integration
        return true;
    }
}
