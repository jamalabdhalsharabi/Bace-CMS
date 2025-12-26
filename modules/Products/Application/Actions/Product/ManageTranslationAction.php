<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Manage Translation Action.
 *
 * Handles product translation operations.
 */
final class ManageTranslationAction extends Action
{
    /**
     * Create or update a product translation.
     *
     * @param Product $product The product
     * @param array $data Translation data
     * @return Product The updated product
     */
    public function execute(Product $product, array $data): Product
    {
        $product->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            [
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]
        );
        
        return $product->fresh(['translations']);
    }
}
