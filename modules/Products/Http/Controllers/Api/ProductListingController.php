<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Http\Resources\ProductResource;

/**
 * Product Listing Controller.
 *
 * Handles all read-only operations for products including listing,
 * viewing, searching, and retrieving product data. This controller
 * follows Single Responsibility Principle by focusing only on queries.
 *
 * @package Modules\Products\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ProductManagementController For CRUD operations
 * @see ProductInventoryController For stock operations
 */
final class ProductListingController extends BaseController
{
    /**
     * Create a new ProductListingController instance.
     *
     * @param ProductQueryService $queryService Service for product read operations
     */
    public function __construct(
        private readonly ProductQueryService $queryService
    ) {}

    /**
     * Display a paginated listing of products.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     *
     * @return JsonResponse Paginated list of products wrapped in ProductResource
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $products = $this->queryService->list(
                $request->only(['status', 'type', 'featured', 'stock_status', 'search']),
                $request->integer('per_page', 20)
            );

            return $this->paginated(ProductResource::collection($products)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve products: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product by its UUID.
     *
     * @param string $id The UUID of the product to retrieve
     *
     * @return JsonResponse The product data wrapped in ProductResource or 404 error
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            return $product
                ? $this->success(new ProductResource($product))
                : $this->notFound('Product not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve product: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the product
     *
     * @return JsonResponse The product data wrapped in ProductResource or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $product = $this->queryService->findBySlug($slug);

            return $product
                ? $this->success(new ProductResource($product))
                : $this->notFound('Product not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve product: ' . $e->getMessage());
        }
    }

    /**
     * Get featured products.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Collection of featured products
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $products = $this->queryService->getFeatured(
                $request->integer('limit', 8)
            );

            return $this->success(ProductResource::collection($products));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve featured products: ' . $e->getMessage());
        }
    }

    /**
     * Get low stock products.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Paginated list of low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $products = $this->queryService->getLowStock(
                $request->integer('per_page', 20)
            );

            return $this->paginated(ProductResource::collection($products)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve low stock products: ' . $e->getMessage());
        }
    }

    /**
     * Get out of stock products.
     *
     * @param Request $request The incoming HTTP request
     *
     * @return JsonResponse Paginated list of out of stock products
     */
    public function outOfStock(Request $request): JsonResponse
    {
        try {
            $products = $this->queryService->getOutOfStock(
                $request->integer('per_page', 20)
            );

            return $this->paginated(ProductResource::collection($products)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve out of stock products: ' . $e->getMessage());
        }
    }
}
