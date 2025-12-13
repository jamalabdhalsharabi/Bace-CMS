<?php

declare(strict_types=1);

namespace Modules\Products\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Product Query Service.
 *
 * Handles read-only operations for products.
 */
final class ProductQueryService
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    /**
     * Get paginated products with filters.
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation', 'prices', 'inventory'])
            ->getPaginated($filters, $perPage);
    }

    /**
     * Find product by ID.
     */
    public function find(string $id): ?Product
    {
        return $this->repository
            ->with(['translations', 'prices', 'inventory', 'variants'])
            ->find($id);
    }

    /**
     * Find product by SKU.
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->repository
            ->with(['translations', 'prices', 'inventory'])
            ->findBySku($sku);
    }

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Product
    {
        return $this->repository
            ->with(['translations', 'prices', 'inventory'])
            ->findBySlug($slug, $locale);
    }

    /**
     * Get published products.
     */
    public function getPublished(int $limit = 20): Collection
    {
        return $this->repository
            ->with(['translation', 'prices'])
            ->getPublished($limit);
    }

    /**
     * Get featured products.
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->repository
            ->with(['translation', 'prices'])
            ->getFeatured($limit);
    }

    /**
     * Get low stock products.
     */
    public function getLowStock(): Collection
    {
        return $this->repository
            ->with(['translation', 'inventory'])
            ->getLowStock();
    }
}
