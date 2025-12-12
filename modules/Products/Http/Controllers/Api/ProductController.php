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

class ProductController extends BaseController
{
    public function __construct(
        protected ProductServiceContract $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->list(
            filters: $request->only(['status', 'type', 'featured', 'stock_status', 'search']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(ProductResource::collection($products)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->success(new ProductResource($product));
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->created(new ProductResource($product), 'Product created successfully');
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->update($product, $request->validated());

        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $this->productService->delete($product);

        return $this->success(null, 'Product deleted successfully');
    }

    public function publish(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->publish($product);

        return $this->success(new ProductResource($product), 'Product published');
    }

    public function unpublish(string $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product = $this->productService->unpublish($product);

        return $this->success(new ProductResource($product), 'Product unpublished');
    }

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
