<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Domain\DTO\ProductData;
use Modules\Products\Http\Requests\CreateProductRequest;
use Modules\Products\Http\Requests\CreateProductTranslationRequest;
use Modules\Products\Http\Requests\DuplicateProductRequest;
use Modules\Products\Http\Requests\ImportProductsRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Http\Resources\ProductResource;

/**
 * Product Management Controller.
 *
 * Handles CRUD operations for products including create, update, delete,
 * duplicate, import/export, and restore operations. This controller follows
 * Single Responsibility Principle by focusing only on management operations.
 *
 * @package Modules\Products\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ProductListingController For read operations
 * @see ProductInventoryController For stock operations
 */
final class ProductManagementController extends BaseController
{
    /**
     * Create a new ProductManagementController instance.
     *
     * @param ProductQueryService $queryService Service for product read operations
     * @param ProductCommandService $commandService Service for product write operations
     */
    public function __construct(
        private readonly ProductQueryService $queryService,
        private readonly ProductCommandService $commandService
    ) {}

    /**
     * Store a newly created product.
     *
     * @param CreateProductRequest $request The validated request containing product data
     *
     * @return JsonResponse The newly created product (HTTP 201)
     *
     * @throws \Throwable When product creation fails
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $productData = new ProductData(
                sku: $validated['sku'],
                barcode: $validated['barcode'] ?? null,
                type: $validated['type'] ?? 'physical',
                status: $validated['status'] ?? 'draft',
                visibility: $validated['visibility'] ?? 'visible',
                is_featured: $validated['is_featured'] ?? false,
                track_inventory: $validated['track_inventory'] ?? true,
                allow_backorder: $validated['allow_backorder'] ?? false,
                requires_shipping: $validated['requires_shipping'] ?? true,
                weight: isset($validated['weight']) ? (float) $validated['weight'] : null,
                weight_unit: $validated['weight_unit'] ?? 'kg',
                tax_class: $validated['tax_class'] ?? null,
                has_variants: false,
                translations: $validated['translations'] ?? [],
                dimensions: $validated['dimensions'] ?? null,
            );
            
            $product = $this->commandService->create($productData);

            return $this->created(new ProductResource($product), 'Product created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product.
     *
     * @param UpdateProductRequest $request The validated request containing updated product data
     * @param string $id The UUID of the product to update
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When product update fails
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $validated = $request->validated();
            $productData = new ProductData(
                sku: $validated['sku'] ?? $product->sku,
                barcode: $validated['barcode'] ?? $product->barcode,
                type: $validated['type'] ?? $product->type,
                status: $validated['status'] ?? $product->status,
                visibility: $validated['visibility'] ?? $product->visibility,
                is_featured: $validated['is_featured'] ?? $product->is_featured,
                track_inventory: $validated['track_inventory'] ?? $product->track_inventory,
                allow_backorder: $validated['allow_backorder'] ?? $product->allow_backorder,
                requires_shipping: $validated['requires_shipping'] ?? $product->requires_shipping,
                weight: isset($validated['weight']) ? (float) $validated['weight'] : $product->weight,
                weight_unit: $validated['weight_unit'] ?? $product->weight_unit,
                tax_class: $validated['tax_class'] ?? $product->tax_class,
                has_variants: $product->has_variants,
                translations: $validated['translations'] ?? [],
                dimensions: $validated['dimensions'] ?? $product->dimensions,
            );

            $product = $this->commandService->update($product, $productData);

            return $this->success(new ProductResource($product), 'Product updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified product.
     *
     * @param string $id The UUID of the product to delete
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When product deletion fails
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->delete($product);

            return $this->success(null, 'Product deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Publish the product.
     *
     * @param string $id The UUID of the product to publish
     *
     * @return JsonResponse The published product or 404 error
     *
     * @throws \Throwable When publishing fails
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->publish($product);

            return $this->success(new ProductResource($product), 'Product published');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish product: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish the product.
     *
     * @param string $id The UUID of the product to unpublish
     *
     * @return JsonResponse The unpublished product or 404 error
     *
     * @throws \Throwable When unpublishing fails
     */
    public function unpublish(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->unpublish($product);

            return $this->success(new ProductResource($product), 'Product unpublished');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish product: ' . $e->getMessage());
        }
    }

    /**
     * Feature the product.
     *
     * @param string $id The UUID of the product to feature
     *
     * @return JsonResponse The featured product or 404 error
     *
     * @throws \Throwable When featuring fails
     */
    public function feature(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->feature($product);

            return $this->success(new ProductResource($product), 'Product featured');
        } catch (\Throwable $e) {
            return $this->error('Failed to feature product: ' . $e->getMessage());
        }
    }

    /**
     * Unfeature the product.
     *
     * @param string $id The UUID of the product to unfeature
     *
     * @return JsonResponse The unfeatured product or 404 error
     *
     * @throws \Throwable When unfeaturing fails
     */
    public function unfeature(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->unfeature($product);

            return $this->success(new ProductResource($product), 'Product unfeatured');
        } catch (\Throwable $e) {
            return $this->error('Failed to unfeature product: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate the product.
     *
     * @param DuplicateProductRequest $request The request containing new_slug
     * @param string $id The UUID of the product to duplicate
     *
     * @return JsonResponse The duplicated product (HTTP 201) or 404 error
     *
     * @throws \Throwable When duplication fails
     */
    public function duplicate(DuplicateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $clone = $this->commandService->duplicate($product, $request->validated()['new_slug']);

            return $this->created(new ProductResource($clone), 'Product duplicated');
        } catch (\Throwable $e) {
            return $this->error('Failed to duplicate product: ' . $e->getMessage());
        }
    }

    /**
     * Archive the product.
     *
     * @param string $id The UUID of the product to archive
     *
     * @return JsonResponse The archived product or 404 error
     *
     * @throws \Throwable When archiving fails
     */
    public function archive(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->archive($product);

            return $this->success(new ProductResource($product), 'Product archived');
        } catch (\Throwable $e) {
            return $this->error('Failed to archive product: ' . $e->getMessage());
        }
    }

    /**
     * Restore from archive.
     *
     * @param string $id The UUID of the product to unarchive
     *
     * @return JsonResponse The restored product or 404 error
     *
     * @throws \Throwable When unarchiving fails
     */
    public function unarchive(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->unarchive($product);

            return $this->success(new ProductResource($product), 'Product restored');
        } catch (\Throwable $e) {
            return $this->error('Failed to unarchive product: ' . $e->getMessage());
        }
    }

    /**
     * Force delete permanently.
     *
     * @param string $id The UUID of the product to permanently delete
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When force deletion fails
     */
    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->findWithTrashed($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->forceDelete($id);

            return $this->success(null, 'Product permanently deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to permanently delete product: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft-deleted product.
     *
     * @param string $id The UUID of the product to restore
     *
     * @return JsonResponse The restored product or 404 error
     *
     * @throws \Throwable When restoration fails
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $product = $this->commandService->restore($id);

            return $product
                ? $this->success(new ProductResource($product), 'Product restored')
                : $this->notFound('Product not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore product: ' . $e->getMessage());
        }
    }

    /**
     * Create translated version.
     *
     * @param CreateProductTranslationRequest $request The validated translation data
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The created translation (HTTP 201) or 404 error
     *
     * @throws \Throwable When translation creation fails
     */
    public function createTranslation(CreateProductTranslationRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $translation = $this->commandService->createTranslation($product, $request->validated());

            return $this->created($translation, 'Translation created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create translation: ' . $e->getMessage());
        }
    }

    /**
     * Import products.
     *
     * @param ImportProductsRequest $request The import request with file
     *
     * @return JsonResponse Import results
     *
     * @throws \Throwable When import fails
     */
    public function import(ImportProductsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->commandService->import(
                $request->file('file'),
                $data['mode'] ?? 'create_update'
            );

            return $this->success($result, 'Products imported');
        } catch (\Throwable $e) {
            return $this->error('Failed to import products: ' . $e->getMessage());
        }
    }

    /**
     * Export products.
     *
     * @param Request $request The export request with filters
     *
     * @return JsonResponse Export results
     *
     * @throws \Throwable When export fails
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $result = $this->commandService->export(
                $request->only(['status', 'category_id']),
                $request->input('format', 'csv')
            );

            return $this->success($result);
        } catch (\Throwable $e) {
            return $this->error('Failed to export products: ' . $e->getMessage());
        }
    }
}
