<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductInventoryService;
use Modules\Products\Application\Services\ProductPricingService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Domain\DTO\ProductData;
use Modules\Products\Http\Requests\CreateProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Http\Resources\ProductResource;

/**
 * Product Controller V2.
 *
 * Uses Clean Architecture with specialized services.
 */
final class ProductControllerV2 extends BaseController
{
    public function __construct(
        private readonly ProductQueryService $queryService,
        private readonly ProductCommandService $commandService,
        private readonly ProductInventoryService $inventoryService,
        private readonly ProductPricingService $pricingService,
    ) {}

    /**
     * List products with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->queryService->list(
            filters: $request->only(['status', 'type', 'featured', 'stock_status', 'search']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(ProductResource::collection($products)->resource);
    }

    /**
     * Show a single product.
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    /**
     * Show product by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->queryService->findBySlug($slug);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    /**
     * Show product by SKU.
     */
    public function showBySku(string $sku): JsonResponse
    {
        $product = $this->queryService->findBySku($sku);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    /**
     * Create a new product.
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $data = ProductData::fromRequest($request);
        $product = $this->commandService->create($data);

        return $this->created(new ProductResource($product), 'Product created');
    }

    /**
     * Update a product.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $data = ProductData::fromRequest($request);
        $product = $this->commandService->update($product, $data);

        return $this->success(new ProductResource($product), 'Product updated');
    }

    /**
     * Delete a product.
     */
    public function destroy(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $this->commandService->delete($product);

        return $this->success(null, 'Product deleted');
    }

    /**
     * Publish a product.
     */
    public function publish(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->commandService->publish($product);

        return $this->success(new ProductResource($product), 'Product published');
    }

    /**
     * Unpublish a product.
     */
    public function unpublish(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->commandService->unpublish($product);

        return $this->success(new ProductResource($product), 'Product unpublished');
    }

    /**
     * Archive a product.
     */
    public function archive(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->commandService->archive($product);

        return $this->success(new ProductResource($product), 'Product archived');
    }

    /**
     * Duplicate a product.
     */
    public function duplicate(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $clone = $this->commandService->duplicate($product);

        return $this->created(new ProductResource($clone), 'Product duplicated');
    }

    /**
     * Restore a deleted product.
     */
    public function restore(string $id): JsonResponse
    {
        $product = $this->commandService->restore($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product), 'Product restored');
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|string|in:adjustment,restock,sale,return',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->inventoryService->updateStock(
            $product,
            $request->integer('quantity'),
            $request->string('type'),
            $request->string('reason')
        );

        return $this->success(new ProductResource($product), 'Stock updated');
    }

    /**
     * Set stock quantity directly.
     */
    public function setStock(Request $request, string $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->inventoryService->setStock($product, $request->integer('quantity'));

        return $this->success(new ProductResource($product), 'Stock set');
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(Request $request, string $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $success = $this->inventoryService->reserveStock($product, $request->integer('quantity'));

        if (!$success) {
            return $this->error('Insufficient stock', 422);
        }

        return $this->success(null, 'Stock reserved');
    }

    /**
     * Set product price.
     */
    public function setPrice(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'currency_id' => 'required|uuid|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'compare_at' => 'nullable|numeric|min:0',
        ]);

        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->pricingService->setPrice(
            $product,
            $request->string('currency_id'),
            (float) $request->amount,
            $request->compare_at ? (float) $request->compare_at : null
        );

        return $this->success(new ProductResource($product), 'Price set');
    }

    /**
     * Apply discount to product.
     */
    public function applyDiscount(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'until' => 'nullable|date|after:now',
        ]);

        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $until = $request->until ? new \DateTime($request->until) : null;
        $product = $this->pricingService->applyDiscount($product, (float) $request->percentage, $until);

        return $this->success(new ProductResource($product), 'Discount applied');
    }

    /**
     * Remove discount from product.
     */
    public function removeDiscount(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->pricingService->removeDiscount($product);

        return $this->success(new ProductResource($product), 'Discount removed');
    }

    /**
     * Get low stock products.
     */
    public function lowStock(): JsonResponse
    {
        $products = $this->queryService->getLowStock();

        return $this->success(ProductResource::collection($products));
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request): JsonResponse
    {
        $products = $this->queryService->getFeatured($request->integer('limit', 10));

        return $this->success(ProductResource::collection($products));
    }
}
