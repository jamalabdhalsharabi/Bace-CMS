<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Http\Requests\CreateProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Http\Requests\UpdateStockRequest;
use Modules\Products\Http\Requests\SetPriceRequest;
use Modules\Products\Http\Requests\SetSalePriceRequest;
use Modules\Products\Http\Requests\AddVariantRequest;
use Modules\Products\Http\Requests\LinkIdsRequest;
use Modules\Products\Http\Requests\BulkUpdatePricesRequest;
use Modules\Products\Http\Resources\ProductResource;

class ProductController extends BaseController
{
    public function __construct(
        protected ProductQueryService $queryService,
        protected ProductCommandService $commandService
    ) {
    }

    /** Display a paginated listing of products. */
    public function index(Request $request): JsonResponse
    {
        $products = $this->queryService->list(
            $request->only(['status', 'type', 'featured', 'stock_status', 'search']),
            $request->integer('per_page', 20)
        );
        return $this->paginated(ProductResource::collection($products)->resource);
    }

    /** Display the specified product by its UUID. */
    public function show(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        return $product ? $this->success(new ProductResource($product)) : $this->notFound('Product not found');
    }

    /** Display the specified product by its URL slug. */
    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->queryService->findBySlug($slug);
        return $product ? $this->success(new ProductResource($product)) : $this->notFound('Product not found');
    }

    /** Store a newly created product. */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->commandService->create($request->validated());
        return $this->created(new ProductResource($product), 'Product created');
    }

    /** Update the specified product. */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->update($product, $request->validated());
        return $this->success(new ProductResource($product), 'Product updated');
    }

    /** Delete the specified product. */
    public function destroy(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->delete($product);
        return $this->success(null, 'Product deleted');
    }

    /** Publish the product. */
    public function publish(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->publish($product);
        return $this->success(new ProductResource($product), 'Product published');
    }

    /** Unpublish the product. */
    public function unpublish(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->unpublish($product);
        return $this->success(new ProductResource($product), 'Product unpublished');
    }

    /** Update stock quantity. */
    public function updateStock(UpdateStockRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->updateStock($product, $request->quantity, $request->type, $request->reason);
        return $this->success(new ProductResource($product), 'Stock updated');
    }

    /** Feature the product. */
    public function feature(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->feature($product);
        return $this->success(new ProductResource($product), 'Product featured');
    }

    /** Unfeature the product. */
    public function unfeature(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->unfeature($product);
        return $this->success(new ProductResource($product), 'Product unfeatured');
    }

    /** Duplicate the product. */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_slug' => 'required|string|max:100']);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $clone = $this->commandService->duplicate($product, $request->new_slug);
        return $this->created(new ProductResource($clone));
    }

    /** Archive the product. */
    public function archive(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->archive($product);
        return $this->success(new ProductResource($product), 'Product archived');
    }

    /** Restore from archive. */
    public function unarchive(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->unarchive($product);
        return $this->success(new ProductResource($product), 'Product restored');
    }

    /** Force delete permanently. */
    public function forceDestroy(string $id): JsonResponse
    {
        $product = $this->queryService->findWithTrashed($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->forceDelete($product);
        return $this->success(null, 'Product permanently deleted');
    }

    /** Restore soft-deleted product. */
    public function restore(string $id): JsonResponse
    {
        $product = $this->commandService->restore($id);
        return $product ? $this->success(new ProductResource($product)) : $this->notFound('Product not found');
    }

    /** Set product price. */
    public function setPrice(SetPriceRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->setPrice($product, $request->validated());
        return $this->success(new ProductResource($product));
    }

    /** Set sale price. */
    public function setSalePrice(SetSalePriceRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->setSalePrice($product, $request->validated());
        return $this->success(new ProductResource($product));
    }

    /** Remove sale price. */
    public function removeSalePrice(string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->removeSalePrice($product);
        return $this->success(new ProductResource($product));
    }

    /** Add product variant. */
    public function addVariant(AddVariantRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $variant = $this->commandService->addVariant($product, $request->validated());
        return $this->created($variant);
    }

    /** Update product variant. */
    public function updateVariant(Request $request, string $id, string $variantId): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $variant = $this->commandService->updateVariant($product, $variantId, $request->all());
        return $this->success($variant);
    }

    /** Delete product variant. */
    public function deleteVariant(string $id, string $variantId): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->deleteVariant($product, $variantId);
        return $this->success(null, 'Variant deleted');
    }

    /** Add image to gallery. */
    public function addGalleryImage(Request $request, string $id): JsonResponse
    {
        $request->validate(['media_id' => 'required|uuid', 'sort_order' => 'nullable|integer']);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->addGalleryImage($product, $request->media_id, $request->integer('sort_order', 0));
        return $this->success(null, 'Image added');
    }

    /** Remove image from gallery. */
    public function removeGalleryImage(string $id, string $mediaId): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->removeGalleryImage($product, $mediaId);
        return $this->success(null, 'Image removed');
    }

    /** Reorder gallery images. */
    public function reorderGallery(Request $request, string $id): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->reorderGallery($product, $request->order);
        return $this->success(null, 'Gallery reordered');
    }

    /** Link categories. */
    public function linkCategories(LinkIdsRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->linkCategories($product, $request->ids);
        return $this->success(null, 'Categories linked');
    }

    /** Link tags. */
    public function linkTags(LinkIdsRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->linkTags($product, $request->ids);
        return $this->success(null, 'Tags linked');
    }

    /** Link related products. */
    public function linkRelated(LinkIdsRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->linkRelated($product, $request->ids);
        return $this->success(null, 'Related products linked');
    }

    /** Link upsell products. */
    public function linkUpsells(LinkIdsRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->linkUpsells($product, $request->ids);
        return $this->success(null, 'Upsell products linked');
    }

    /** Link cross-sell products. */
    public function linkCrossSells(LinkIdsRequest $request, string $id): JsonResponse
    {
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $this->commandService->linkCrossSells($product, $request->ids);
        return $this->success(null, 'Cross-sell products linked');
    }

    /** Set stock tracking. */
    public function setStockTracking(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'track_stock' => 'required|boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->setStockTracking($product, $request->validated());
        return $this->success(new ProductResource($product));
    }

    /** Set backorder settings. */
    public function setBackorderSettings(Request $request, string $id): JsonResponse
    {
        $request->validate(['allow_backorders' => 'required|in:no,notify,yes']);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $product = $this->commandService->setBackorderSettings($product, $request->allow_backorders);
        return $this->success(new ProductResource($product));
    }

    /** Create translated version. */
    public function createTranslation(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
        ]);
        $product = $this->queryService->find($id);
        if (!$product) return $this->notFound('Product not found');
        $translation = $this->commandService->createTranslation($product, $request->validated());
        return $this->created($translation);
    }

    /** Import products. */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
            'mode' => 'nullable|in:create,update,create_update',
        ]);
        $result = $this->commandService->import($request->file('file'), $request->input('mode', 'create_update'));
        return $this->success($result, 'Products imported');
    }

    /** Export products. */
    public function export(Request $request): JsonResponse
    {
        $result = $this->commandService->export(
            $request->only(['status', 'category_id']),
            $request->input('format', 'csv')
        );
        return $this->success($result);
    }

    /** Get featured products. */
    public function featured(Request $request): JsonResponse
    {
        $products = $this->queryService->getFeatured($request->integer('limit', 8));
        return $this->success(ProductResource::collection($products));
    }

    /** Get low stock products. */
    public function lowStock(Request $request): JsonResponse
    {
        $products = $this->queryService->getLowStock($request->integer('per_page', 20));
        return $this->paginated(ProductResource::collection($products)->resource);
    }

    /** Get out of stock products. */
    public function outOfStock(Request $request): JsonResponse
    {
        $products = $this->queryService->getOutOfStock($request->integer('per_page', 20));
        return $this->paginated(ProductResource::collection($products)->resource);
    }

    /** Bulk update prices. */
    public function bulkUpdatePrices(BulkUpdatePricesRequest $request): JsonResponse
    {
        $count = $this->commandService->bulkUpdatePrices($request->validated());
        return $this->success(['updated' => $count], 'Prices updated');
    }

    /** Bulk update stock. */
    public function bulkUpdateStock(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|uuid',
            'updates.*.quantity' => 'required|integer',
        ]);
        $count = $this->commandService->bulkUpdateStock($request->updates);
        return $this->success(['updated' => $count], 'Stock updated');
    }
}
