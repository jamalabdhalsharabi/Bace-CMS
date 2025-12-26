<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Variant;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Manage Gallery Action.
 *
 * Handles gallery image operations for products.
 */
final class ManageGalleryAction extends Action
{
    /**
     * Add an image to product gallery.
     *
     * @param Product $product The product
     * @param string $mediaId The media ID
     * @param int $sortOrder The sort order
     * @return void
     */
    public function execute(Product $product, string $mediaId, int $sortOrder = 0): void
    {
        $product->media()->attach($mediaId, ['sort_order' => $sortOrder]);
    }

    /**
     * Remove an image from product gallery.
     *
     * @param Product $product The product
     * @param string $mediaId The media ID
     * @return void
     */
    public function remove(Product $product, string $mediaId): void
    {
        $product->media()->detach($mediaId);
    }

    /**
     * Reorder gallery images.
     *
     * @param Product $product The product
     * @param array $order Array of media IDs in desired order
     * @return void
     */
    public function reorder(Product $product, array $order): void
    {
        foreach ($order as $index => $mediaId) {
            $product->media()->updateExistingPivot($mediaId, ['sort_order' => $index]);
        }
    }
}
