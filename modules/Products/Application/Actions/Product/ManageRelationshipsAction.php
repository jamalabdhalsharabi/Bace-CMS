<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Manage Relationships Action.
 *
 * Handles product relationships (categories, tags, related products, etc.).
 */
final class ManageRelationshipsAction extends Action
{
    /**
     * Link categories to a product.
     *
     * @param Product $product The product
     * @param array $ids Category IDs
     * @return void
     */
    public function linkCategories(Product $product, array $ids): void
    {
        if (method_exists($product, 'categories')) {
            $product->categories()->sync($ids);
        }
    }

    /**
     * Link tags to a product.
     *
     * @param Product $product The product
     * @param array $ids Tag IDs
     * @return void
     */
    public function linkTags(Product $product, array $ids): void
    {
        if (method_exists($product, 'tags')) {
            $product->tags()->sync($ids);
        }
    }

    /**
     * Link related products.
     *
     * @param Product $product The product
     * @param array $ids Related product IDs
     * @return void
     */
    public function linkRelated(Product $product, array $ids): void
    {
        if (method_exists($product, 'relatedProducts')) {
            $product->relatedProducts()->sync($ids);
        }
    }

    /**
     * Link upsell products.
     *
     * @param Product $product The product
     * @param array $ids Upsell product IDs
     * @return void
     */
    public function linkUpsells(Product $product, array $ids): void
    {
        if (method_exists($product, 'upsellProducts')) {
            $product->upsellProducts()->sync($ids);
        }
    }

    /**
     * Link cross-sell products.
     *
     * @param Product $product The product
     * @param array $ids Cross-sell product IDs
     * @return void
     */
    public function linkCrossSells(Product $product, array $ids): void
    {
        if (method_exists($product, 'crossSellProducts')) {
            $product->crossSellProducts()->sync($ids);
        }
    }
}
