<?php

declare(strict_types=1);

namespace Modules\Products\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\Domain\Models\Product;

class ProductService implements ProductServiceContract
{
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

    public function find(string $id): ?Product
    {
        return Product::with(['translations', 'variants.prices', 'prices', 'inventory'])->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::findBySlug($slug)?->load(['translations', 'variants', 'prices']);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

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

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function publish(Product $product): Product
    {
        $product->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        return $product->fresh();
    }

    public function unpublish(Product $product): Product
    {
        $product->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
        return $product->fresh();
    }

    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product
    {
        if ($product->inventory) {
            $product->inventory->adjustStock($quantity, $type, $reason);
            $this->updateStockStatus($product);
        }
        return $product->fresh(['inventory']);
    }

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

    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    public function restore(string $id): ?Product
    {
        $product = Product::withTrashed()->find($id);
        $product?->restore();
        return $product;
    }

    public function saveDraft(Product $product, array $data): Product
    {
        $data['status'] = 'draft';
        return $this->update($product, $data);
    }

    public function submitForReview(Product $product): Product
    {
        $product->update(['status' => 'pending_review']);
        return $product->fresh();
    }

    public function approve(Product $product): Product
    {
        $product->update(['status' => 'approved']);
        return $product->fresh();
    }

    public function reject(Product $product, ?string $reason = null): Product
    {
        $product->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $product->fresh();
    }

    public function schedule(Product $product, \DateTime $date): Product
    {
        $product->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $product->fresh();
    }

    public function archive(Product $product): Product
    {
        $product->update(['status' => 'archived', 'archived_at' => now()]);
        return $product->fresh();
    }

    public function unarchive(Product $product): Product
    {
        $product->update(['status' => 'draft', 'archived_at' => null]);
        return $product->fresh();
    }

    public function discontinue(Product $product): Product
    {
        $product->update(['status' => 'discontinued']);
        return $product->fresh();
    }

    public function addVariant(Product $product, array $data): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->create($data);
        $product->update(['has_variants' => true]);
        return $variant;
    }

    public function updateVariant(Product $product, string $variantId, array $data): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->findOrFail($variantId);
        $variant->update($data);
        return $variant->fresh();
    }

    public function deleteVariant(Product $product, string $variantId): bool
    {
        return $product->variants()->where('id', $variantId)->delete() > 0;
    }

    public function toggleVariant(Product $product, string $variantId, bool $active): \Modules\Products\Domain\Models\ProductVariant
    {
        $variant = $product->variants()->findOrFail($variantId);
        $variant->update(['is_active' => $active]);
        return $variant->fresh();
    }

    public function setPrice(Product $product, string $currencyId, float $amount, ?float $compareAt = null): Product
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            ['amount' => $amount, 'compare_at_price' => $compareAt]
        );
        return $product->fresh(['prices']);
    }

    public function updatePrice(Product $product, string $currencyId, float $amount): Product
    {
        $product->prices()->where('currency_id', $currencyId)->update(['amount' => $amount]);
        return $product->fresh(['prices']);
    }

    public function schedulePrice(Product $product, string $currencyId, float $amount, \DateTime $startAt, ?\DateTime $endAt = null): Product
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            ['scheduled_amount' => $amount, 'price_starts_at' => $startAt, 'price_ends_at' => $endAt]
        );
        return $product->fresh(['prices']);
    }

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

    public function reserveStock(Product $product, int $quantity, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->reserve($quantity, $orderId);
    }

    public function releaseReservation(Product $product, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->releaseReservation($orderId);
    }

    public function confirmReservation(Product $product, string $orderId): bool
    {
        if (!$product->inventory) return false;
        return $product->inventory->confirmReservation($orderId);
    }

    public function setLowStockThreshold(Product $product, int $threshold): Product
    {
        $product->inventory?->update(['low_stock_threshold' => $threshold]);
        return $product->fresh(['inventory']);
    }

    public function enablePreorder(Product $product): Product
    {
        $product->update(['allow_backorder' => true, 'is_preorder' => true]);
        return $product->fresh();
    }

    public function syncCategories(Product $product, array $categoryIds): Product
    {
        $product->categories()->sync($categoryIds);
        return $product->fresh(['categories']);
    }

    public function attachRelated(Product $product, array $productIds): Product
    {
        $product->relatedProducts()->sync($productIds);
        return $product->fresh(['relatedProducts']);
    }

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

    public function export(array $filters = []): array
    {
        return Product::with(['translations', 'prices', 'inventory'])
            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->get()
            ->map(fn($p) => $p->toArray())
            ->toArray();
    }

    public function syncExternal(Product $product, string $channel): bool
    {
        // Implementation depends on external channel integration
        return true;
    }
}
