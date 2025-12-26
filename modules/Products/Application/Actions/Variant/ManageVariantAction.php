<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Variant;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Manage Variant Action.
 *
 * Handles variant CRUD operations for products.
 */
final class ManageVariantAction extends Action
{
    /**
     * Add a variant to a product.
     *
     * @param Product $product The product
     * @param array $data Variant data
     * @return mixed The created variant
     */
    public function execute(Product $product, array $data): mixed
    {
        return $product->variants()->create($data);
    }

    /**
     * Update a product variant.
     *
     * @param Product $product The product
     * @param string $variantId The variant ID
     * @param array $data Update data
     * @return mixed The updated variant
     */
    public function update(Product $product, string $variantId, array $data): mixed
    {
        $variant = $product->variants()->find($variantId);
        
        if ($variant) {
            $variant->update($data);
        }
        
        return $variant;
    }

    /**
     * Delete a product variant.
     *
     * @param Product $product The product
     * @param string $variantId The variant ID
     * @return bool Whether deletion was successful
     */
    public function delete(Product $product, string $variantId): bool
    {
        return (bool) $product->variants()->where('id', $variantId)->delete();
    }
}
