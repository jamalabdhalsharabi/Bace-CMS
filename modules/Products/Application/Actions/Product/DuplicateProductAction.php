<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

final class DuplicateProductAction extends Action
{
    public function execute(Product $product): Product
    {
        return $this->transaction(function () use ($product) {
            $clone = $product->replicate(['status', 'published_at', 'sku']);
            $clone->status = 'draft';
            $clone->sku = $product->sku . '-copy-' . time();
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($product->translations as $trans) {
                $clone->translations()->create([
                    'locale' => $trans->locale,
                    'name' => $trans->name . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'short_description' => $trans->short_description,
                    'description' => $trans->description,
                    'meta_title' => $trans->meta_title,
                    'meta_description' => $trans->meta_description,
                ]);
            }

            // Sync categories if relationship exists
            if (method_exists($clone, 'categories') && $product->relationLoaded('categories')) {
                try {
                    $clone->categories()->sync($product->categories->pluck('id'));
                } catch (\Throwable $e) {
                    // Skip if categories table doesn't exist
                }
            }

            foreach ($product->variants as $variant) {
                $cloneVariant = $clone->variants()->create($variant->only([
                    'sku', 'price', 'stock_quantity', 'attributes'
                ]));
                $cloneVariant->update(['sku' => $variant->sku . '-copy-' . time()]);
            }

            return $clone->fresh(['translations']);
        });
    }
}
