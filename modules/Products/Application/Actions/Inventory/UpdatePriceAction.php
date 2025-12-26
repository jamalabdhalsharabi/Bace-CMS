<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Inventory;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Update Price Action.
 *
 * Handles pricing operations for products.
 */
final class UpdatePriceAction extends Action
{
    /**
     * Set product price.
     *
     * @param Product $product The product to update
     * @param array $data Price data (price, currency_code, compare_at_price)
     * @return Product The updated product
     */
    public function execute(Product $product, array $data): Product
    {
        $currencyId = $data['currency_id'] ?? null;
        
        if (!$currencyId) {
            $currency = DB::table('currencies')
                ->where('code', $data['currency_code'] ?? 'USD')
                ->first() ?? DB::table('currencies')->first();
            $currencyId = $currency?->id;
        }
        
        if ($currencyId) {
            $product->prices()->updateOrCreate(
                ['currency_id' => $currencyId],
                [
                    'amount' => $data['price'],
                    'compare_at_amount' => $data['compare_at_price'] ?? null,
                ]
            );
        }
        
        return $product->fresh(['prices']);
    }

    /**
     * Set sale price.
     *
     * @param Product $product The product to update
     * @param float $salePrice The sale price
     * @return Product The updated product
     */
    public function setSalePrice(Product $product, float $salePrice): Product
    {
        $price = $product->prices()->first();
        
        if ($price) {
            $price->update([
                'compare_at_amount' => $price->amount,
                'amount' => $salePrice,
            ]);
        }
        
        return $product->fresh(['prices']);
    }

    /**
     * Remove sale price.
     *
     * @param Product $product The product to update
     * @return Product The updated product
     */
    public function removeSalePrice(Product $product): Product
    {
        $price = $product->prices()->first();
        
        if ($price && $price->compare_at_amount) {
            $price->update([
                'amount' => $price->compare_at_amount,
                'compare_at_amount' => null,
            ]);
        }
        
        return $product->fresh(['prices']);
    }

    /**
     * Bulk update prices for multiple products.
     *
     * @param array $data Bulk update data
     * @return int Number of products updated
     */
    public function bulkUpdate(array $data): int
    {
        $count = 0;
        
        foreach ($data['products'] ?? [] as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $this->execute($product, $item);
                $count++;
            }
        }
        
        return $count;
    }
}
