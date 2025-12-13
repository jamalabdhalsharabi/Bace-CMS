<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\DTO\ProductData;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Update Product Action.
 */
final class UpdateProductAction extends Action
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    public function execute(Product $product, ProductData $data): Product
    {
        return $this->transaction(function () use ($product, $data) {
            $this->repository->update($product->id, [
                'barcode' => $data->barcode ?? $product->barcode,
                'type' => $data->type,
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
                'updated_by' => $this->userId(),
            ]);

            $this->updateTranslations($product, $data->translations);

            return $product->fresh(['translations', 'prices', 'inventory']);
        });
    }

    private function updateTranslations(Product $product, array $translations): void
    {
        foreach ($translations as $locale => $trans) {
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
}
