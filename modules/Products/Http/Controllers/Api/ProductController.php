<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\Http\Requests\CreateProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Http\Resources\ProductResource;

/**
 * Class ProductController
 * 
 * API controller for managing products including CRUD,
 * workflow, pricing, inventory, and stock management.
 * 
 * @package Modules\Products\Http\Controllers\Api
 */
class ProductController extends BaseController
{
    /**
     * The product service instance for handling product-related business logic.
     *
     * @var ProductServiceContract
     */
    protected ProductServiceContract $productService;

    /**
     * Create a new ProductController instance.
     *
     * @param ProductServiceContract $productService The product service contract implementation
     */
    public function __construct(
        ProductServiceContract $productService
    ) {
        $this->productService = $productService;
    }

    /**
     * Display a paginated listing of products.
     *
     * Supports filtering by status, type, featured flag, stock status, and search term.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of products wrapped in ProductResource
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->list(
            filters: $request->only(['status', 'type', 'featured', 'stock_status', 'search']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(ProductResource::collection($products)->resource);
    }

    /**
     * Display the specified product by its UUID.
     *
     * @param string $id The UUID of the product to retrieve
     * @return JsonResponse The product data wrapped in ProductResource or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    /**
     * Display the specified product by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the product
     * @return JsonResponse The product data wrapped in ProductResource or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    /**
     * Store a newly created product in the database.
     *
     * @param CreateProductRequest $request The validated request containing product data
     * @return JsonResponse The newly created product wrapped in ProductResource (HTTP 201)
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->created(new ProductResource($product), 'Product created successfully');
    }

    /**
     * Update the specified product in the database.
     *
     * @param UpdateProductRequest $request The validated request containing updated product data
     * @param string $id The UUID of the product to update
     * @return JsonResponse The updated product wrapped in ProductResource or 404 error
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->update($product, $request->validated());

        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    /**
     * Delete the specified product.
     *
     * @param string $id The UUID of the product to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $this->productService->delete($product);

        return $this->success(null, 'Product deleted successfully');
    }

    /**
     * Publish the specified product, making it available for purchase.
     *
     * @param string $id The UUID of the product to publish
     * @return JsonResponse The published product or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->publish($product);

        return $this->success(new ProductResource($product), 'Product published');
    }

    /**
     * Unpublish the specified product.
     *
     * Removes the product from public view and purchase availability.
     *
     * @param string $id The UUID of the product to unpublish
     * @return JsonResponse The unpublished product or 404 error
     */
    public function unpublish(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->unpublish($product);

        return $this->success(new ProductResource($product), 'Product unpublished');
    }

    /**
     * Update the stock quantity for a product.
     *
     * Supports different stock movement types: adjustment, restock, sale, return.
     *
     * @param Request $request The request containing quantity, type, and optional reason
     * @param string $id The UUID of the product
     * @return JsonResponse The updated product or 404 error
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|string|in:adjustment,restock,sale,return',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->updateStock(
            $product,
            $request->quantity,
            $request->type,
            $request->reason
        );

        return $this->success(new ProductResource($product), 'Stock updated');
    }
}
