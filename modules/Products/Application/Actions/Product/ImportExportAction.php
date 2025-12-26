<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;

/**
 * Import Export Action.
 *
 * Handles product import and export operations.
 */
final class ImportExportAction extends Action
{
    /**
     * Import products from data array.
     *
     * @param array $data Import data
     * @return array Import results with counts and errors
     */
    public function import(array $data): array
    {
        $imported = 0;
        $errors = [];
        
        foreach ($data['products'] ?? [] as $productData) {
            try {
                Product::create($productData);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Export products with optional filters.
     *
     * @param array $filters Export filters
     * @return Collection The exported products
     */
    public function export(array $filters = []): Collection
    {
        $query = Product::with(['translations', 'prices', 'inventory']);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->get();
    }
}
