<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Http\Requests\AddGalleryImageRequest;
use Modules\Products\Http\Requests\AddVariantRequest;
use Modules\Products\Http\Requests\LinkIdsRequest;
use Modules\Products\Http\Requests\ReorderGalleryRequest;

/**
 * Product Variant Controller.
 *
 * Handles variant-related operations for products including
 * variants, gallery images, and product relationships (categories, tags, etc.).
 *
 * @package Modules\Products\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ProductListingController For read operations
 * @see ProductManagementController For CRUD operations
 */
final class ProductVariantController extends BaseController
{
    /**
     * Create a new ProductVariantController instance.
     *
     * @param ProductQueryService $queryService Service for product read operations
     * @param ProductCommandService $commandService Service for product write operations
     */
    public function __construct(
        private readonly ProductQueryService $queryService,
        private readonly ProductCommandService $commandService
    ) {}

    /**
     * Add product variant.
     *
     * @param AddVariantRequest $request The validated variant request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The created variant (HTTP 201) or 404 error
     *
     * @throws \Throwable When variant creation fails
     */
    public function addVariant(AddVariantRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $variant = $this->commandService->addVariant($product, $request->validated());

            return $this->created($variant, 'Variant added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add variant: ' . $e->getMessage());
        }
    }

    /**
     * Update product variant.
     *
     * @param Request $request The request containing variant data
     * @param string $id The UUID of the product
     * @param string $variantId The UUID of the variant
     *
     * @return JsonResponse The updated variant or 404 error
     *
     * @throws \Throwable When variant update fails
     */
    public function updateVariant(Request $request, string $id, string $variantId): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $variant = $this->commandService->updateVariant($product, $variantId, $request->all());

            return $this->success($variant, 'Variant updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update variant: ' . $e->getMessage());
        }
    }

    /**
     * Delete product variant.
     *
     * @param string $id The UUID of the product
     * @param string $variantId The UUID of the variant
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When variant deletion fails
     */
    public function deleteVariant(string $id, string $variantId): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->deleteVariant($product, $variantId);

            return $this->success(null, 'Variant deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete variant: ' . $e->getMessage());
        }
    }

    /**
     * Add image to gallery.
     *
     * @param AddGalleryImageRequest $request The validated gallery image request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When image addition fails
     */
    public function addGalleryImage(AddGalleryImageRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $data = $request->validated();
            $this->commandService->addGalleryImage($product, $data['media_id'], $data['sort_order'] ?? 0);

            return $this->success(null, 'Image added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add image: ' . $e->getMessage());
        }
    }

    /**
     * Remove image from gallery.
     *
     * @param string $id The UUID of the product
     * @param string $mediaId The UUID of the media
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When image removal fails
     */
    public function removeGalleryImage(string $id, string $mediaId): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->removeGalleryImage($product, $mediaId);

            return $this->success(null, 'Image removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove image: ' . $e->getMessage());
        }
    }

    /**
     * Reorder gallery images.
     *
     * @param ReorderGalleryRequest $request The validated reorder request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When reorder fails
     */
    public function reorderGallery(ReorderGalleryRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->reorderGallery($product, $request->validated()['order']);

            return $this->success(null, 'Gallery reordered');
        } catch (\Throwable $e) {
            return $this->error('Failed to reorder gallery: ' . $e->getMessage());
        }
    }

    /**
     * Link categories.
     *
     * @param LinkIdsRequest $request The validated link request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When linking fails
     */
    public function linkCategories(LinkIdsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->linkCategories($product, $request->ids);

            return $this->success(null, 'Categories linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link categories: ' . $e->getMessage());
        }
    }

    /**
     * Link tags.
     *
     * @param LinkIdsRequest $request The validated link request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When linking fails
     */
    public function linkTags(LinkIdsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->linkTags($product, $request->ids);

            return $this->success(null, 'Tags linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link tags: ' . $e->getMessage());
        }
    }

    /**
     * Link related products.
     *
     * @param LinkIdsRequest $request The validated link request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When linking fails
     */
    public function linkRelated(LinkIdsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->linkRelated($product, $request->ids);

            return $this->success(null, 'Related products linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link related products: ' . $e->getMessage());
        }
    }

    /**
     * Link upsell products.
     *
     * @param LinkIdsRequest $request The validated link request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When linking fails
     */
    public function linkUpsells(LinkIdsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->linkUpsells($product, $request->ids);

            return $this->success(null, 'Upsell products linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link upsell products: ' . $e->getMessage());
        }
    }

    /**
     * Link cross-sell products.
     *
     * @param LinkIdsRequest $request The validated link request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse Success message or 404 error
     *
     * @throws \Throwable When linking fails
     */
    public function linkCrossSells(LinkIdsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $this->commandService->linkCrossSells($product, $request->ids);

            return $this->success(null, 'Cross-sell products linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link cross-sell products: ' . $e->getMessage());
        }
    }
}
