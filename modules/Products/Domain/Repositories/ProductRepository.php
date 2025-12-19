<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Products\Domain\Models\Product;

/**
 * Product Repository.
 *
 * Read-only repository for Product model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Product>
 *
 * @package Modules\Products\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProductRepository extends BaseRepository
{
    /**
     * Create a new ProductRepository instance.
     *
     * @param Product $model The Product model instance
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated products with filters.
     *
     * @param array<string, mixed> $filters Filter criteria
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['stock_status'])) {
            $query->where('stock_status', $filters['stock_status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => 
                $q->where('sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('translations', fn ($t) => 
                      $t->where('name', 'LIKE', "%{$search}%")
                  )
            );
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find product by SKU.
     *
     * @param string $sku Product SKU
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->query()->where('sku', $sku)->first();
    }

    /**
     * Find product by slug.
     *
     * @param string $slug URL slug
     * @param string|null $locale Locale code
     * @return Product|null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Product
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }

    /**
     * Get published products.
     *
     * @param int $limit Maximum number
     * @return Collection<int, Product>
     */
    public function getPublished(int $limit = 20): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('visibility', 'visible')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured products.
     *
     * @param int $limit Maximum number
     * @return Collection<int, Product>
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get in-stock products.
     *
     * @param int $limit Maximum number
     * @return Collection<int, Product>
     */
    public function getInStock(int $limit = 20): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('stock_status', 'in_stock')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low stock products.
     *
     * @return Collection<int, Product>
     */
    public function getLowStock(): Collection
    {
        return $this->query()
            ->whereHas('inventory', fn ($q) => 
                $q->whereColumn('quantity', '<=', 'low_stock_threshold')
            )
            ->get();
    }
}
