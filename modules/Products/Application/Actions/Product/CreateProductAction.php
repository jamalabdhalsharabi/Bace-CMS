<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\DTO\ProductData;
use Modules\Products\Domain\Events\ProductCreated;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Create Product Action.
 *
 * Handles the creation of a new product with translations.
 */
final class CreateProductAction extends Action
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    /**
     * Execute the action.
     *
     * @param ProductData $data Product data DTO
     * @return Product The created product
     */
    public function execute(ProductData $data): Product
    {
        return $this->transaction(function () use ($data) {
            $product = $this->repository->create([
                'sku' => $data->sku,
                'barcode' => $data->barcode,
                'type' => $data->type,
                'status' => $data->status,
                'visibility' => $data->visibility,
                'is_featured' => $data->is_featured,
                'track_inventory' => $data->track_inventory,
                'allow_backorder' => $data->allow_backorder,
                'requires_shipping' => $data->requires_shipping,
                'weight' => $data->weight,
                'weight_unit' => $data->weight_unit,
                'tax_class' => $data->tax_class,
                'has_variants' => $data->has_variants,
                'dimensions' => $data->dimensions,
                'meta' => $data->meta,
                'created_by' => $this->userId(),
            ]);

            $this->createTranslations($product, $data->translations);
            $this->createDefaultInventory($product, $data->track_inventory);

            event(new ProductCreated($product));

            return $product->fresh(['translations', 'inventory', 'prices']);
        });
    }

    /**
     * Create translations for the product.
     */
    private function createTranslations(Product $product, array $translations): void
    {
        foreach ($translations as $locale => $trans) {
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

    /**
     * Create default inventory record.
     */
    private function createDefaultInventory(Product $product, bool $trackInventory): void
    {
        if ($trackInventory) {
            $product->inventory()->create([
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
            ]);
        }
    }
}
