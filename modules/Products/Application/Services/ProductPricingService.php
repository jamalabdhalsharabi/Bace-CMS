<?php

declare(strict_types=1);

namespace Modules\Products\Application\Services;

use DateTime;
use Modules\Products\Domain\Models\Product;

/**
 * Product Pricing Service.
 *
 * Manages product pricing, discounts, and scheduled prices.
 * Single Responsibility: Pricing operations.
 */
final class ProductPricingService
{
    /**
     * Set price for a currency.
     *
     * @param Product $product The product
     * @param string $currencyId Currency UUID
     * @param float $amount Price amount
     * @param float|null $compareAt Compare-at price
     * @return Product
     */
    public function setPrice(Product $product, string $currencyId, float $amount, ?float $compareAt = null): Product
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            ['amount' => $amount, 'compare_at_amount' => $compareAt]
        );

        return $product->fresh(['prices']);
    }

    /**
     * Schedule a price change.
     *
     * @param Product $product The product
     * @param string $currencyId Currency UUID
     * @param float $amount Scheduled price
     * @param DateTime $startAt Start date
     * @param DateTime|null $endAt End date
     * @return Product
     */
    public function schedulePrice(
        Product $product, 
        string $currencyId, 
        float $amount, 
        DateTime $startAt, 
        ?DateTime $endAt = null
    ): Product {
        $product->prices()->updateOrCreate(
            ['currency_id' => $currencyId],
            [
                'amount' => $amount,
                'starts_at' => $startAt,
                'ends_at' => $endAt,
            ]
        );

        return $product->fresh(['prices']);
    }

    /**
     * Apply percentage discount to all prices.
     *
     * @param Product $product The product
     * @param float $percentage Discount percentage (0-100)
     * @param DateTime|null $until Discount end date
     * @return Product
     */
    public function applyDiscount(Product $product, float $percentage, ?DateTime $until = null): Product
    {
        foreach ($product->prices as $price) {
            $discountedAmount = $price->amount * (1 - $percentage / 100);
            $price->update([
                'compare_at_amount' => $price->amount,
                'amount' => round($discountedAmount, 2),
                'ends_at' => $until,
            ]);
        }

        return $product->fresh(['prices']);
    }

    /**
     * Remove all discounts.
     *
     * @param Product $product The product
     * @return Product
     */
    public function removeDiscount(Product $product): Product
    {
        foreach ($product->prices as $price) {
            if ($price->compare_at_amount) {
                $price->update([
                    'amount' => $price->compare_at_amount,
                    'compare_at_amount' => null,
                    'ends_at' => null,
                ]);
            }
        }

        return $product->fresh(['prices']);
    }

    /**
     * Get current price for a currency.
     *
     * @param Product $product The product
     * @param string $currencyId Currency UUID
     * @return float|null
     */
    public function getPrice(Product $product, string $currencyId): ?float
    {
        $price = $product->prices()->where('currency_id', $currencyId)->first();

        return $price?->amount;
    }

    /**
     * Check if product has active discount.
     *
     * @param Product $product The product
     * @return bool
     */
    public function hasDiscount(Product $product): bool
    {
        return $product->prices()->whereNotNull('compare_at_amount')->exists();
    }
}
