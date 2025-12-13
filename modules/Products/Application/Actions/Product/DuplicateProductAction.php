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
                    'title' => $trans->title . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'description' => $trans->description,
                    'content' => $trans->content,
                ]);
            }

            $clone->categories()->sync($product->categories->pluck('id'));

            foreach ($product->variants as $variant) {
                $cloneVariant = $clone->variants()->create($variant->only([
                    'sku', 'price', 'stock_quantity', 'attributes'
                ]));
                $cloneVariant->update(['sku' => $variant->sku . '-copy-' . time()]);
            }

            return $clone->fresh(['translations', 'categories', 'variants']);
        });
    }
}
