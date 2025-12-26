<?php

declare(strict_types=1);

namespace Modules\Products\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Products\Application\Actions\Inventory\UpdatePriceAction;
use Modules\Products\Application\Actions\Inventory\UpdateStockAction;
use Modules\Products\Application\Actions\Product\CreateProductAction;
use Modules\Products\Application\Actions\Product\DeleteProductAction;
use Modules\Products\Application\Actions\Product\DuplicateProductAction;
use Modules\Products\Application\Actions\Product\FeatureProductAction;
use Modules\Products\Application\Actions\Product\ImportExportAction;
use Modules\Products\Application\Actions\Product\ManageRelationshipsAction;
use Modules\Products\Application\Actions\Product\ManageTranslationAction;
use Modules\Products\Application\Actions\Product\PublishProductAction;
use Modules\Products\Application\Actions\Product\UpdateProductAction;
use Modules\Products\Application\Actions\Variant\ManageGalleryAction;
use Modules\Products\Application\Actions\Variant\ManageVariantAction;
use Modules\Products\Domain\DTO\ProductData;
use Modules\Products\Domain\Models\Product;

/**
 * Product Command Service.
 *
 * Orchestrates all write operations for products via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Products\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProductCommandService
{
    /**
     * Create a new ProductCommandService instance.
     */
    public function __construct(
        private readonly CreateProductAction $createAction,
        private readonly UpdateProductAction $updateAction,
        private readonly PublishProductAction $publishAction,
        private readonly DeleteProductAction $deleteAction,
        private readonly DuplicateProductAction $duplicateAction,
        private readonly FeatureProductAction $featureAction,
        private readonly UpdateStockAction $stockAction,
        private readonly UpdatePriceAction $priceAction,
        private readonly ManageVariantAction $variantAction,
        private readonly ManageGalleryAction $galleryAction,
        private readonly ManageRelationshipsAction $relationshipsAction,
        private readonly ManageTranslationAction $translationAction,
        private readonly ImportExportAction $importExportAction,
    ) {}

    public function create(ProductData $data): Product
    {
        return $this->createAction->execute($data);
    }

    public function update(Product $product, ProductData $data): Product
    {
        return $this->updateAction->execute($product, $data);
    }

    public function publish(Product $product): Product
    {
        return $this->publishAction->execute($product);
    }

    public function unpublish(Product $product): Product
    {
        return $this->publishAction->unpublish($product);
    }

    public function archive(Product $product): Product
    {
        return $this->publishAction->archive($product);
    }

    public function unarchive(Product $product): Product
    {
        return $this->publishAction->unarchive($product);
    }

    public function delete(Product $product): bool
    {
        return $this->deleteAction->execute($product);
    }

    public function forceDelete(string $id): bool
    {
        return $this->deleteAction->forceDelete($id);
    }

    public function restore(string $id): ?Product
    {
        return $this->deleteAction->restore($id);
    }

    public function duplicate(Product $product): Product
    {
        return $this->duplicateAction->execute($product);
    }

    public function feature(Product $product): Product
    {
        return $this->featureAction->execute($product);
    }

    public function unfeature(Product $product): Product
    {
        return $this->featureAction->unfeature($product);
    }

    // ========== Inventory Methods ==========

    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product
    {
        return $this->stockAction->execute($product, $quantity, $type, $reason);
    }

    public function setPrice(Product $product, array $data): Product
    {
        return $this->priceAction->execute($product, $data);
    }

    public function setSalePrice(Product $product, array $data): Product
    {
        return $this->priceAction->setSalePrice($product, $data['sale_price']);
    }

    public function removeSalePrice(Product $product): Product
    {
        return $this->priceAction->removeSalePrice($product);
    }

    public function setStockTracking(Product $product, array $data): Product
    {
        return $this->stockAction->setTracking(
            $product,
            $data['track_stock'] ?? true,
            $data['low_stock_threshold'] ?? null
        );
    }

    public function setBackorderSettings(Product $product, string $allowBackorders): Product
    {
        return $this->stockAction->setBackorder($product, $allowBackorders);
    }

    public function bulkUpdatePrices(array $data): int
    {
        return $this->priceAction->bulkUpdate($data);
    }

    public function bulkUpdateStock(array $updates): int
    {
        return $this->stockAction->bulkUpdate($updates);
    }

    // ========== Translation Method ==========

    public function createTranslation(Product $product, array $data): Product
    {
        return $this->translationAction->execute($product, $data);
    }

    // ========== Import/Export Methods ==========

    public function import(array $data): array
    {
        return $this->importExportAction->import($data);
    }

    public function export(array $filters = []): Collection
    {
        return $this->importExportAction->export($filters);
    }

    // ========== Variant Methods ==========

    public function addVariant(Product $product, array $data): mixed
    {
        return $this->variantAction->execute($product, $data);
    }

    public function updateVariant(Product $product, string $variantId, array $data): mixed
    {
        return $this->variantAction->update($product, $variantId, $data);
    }

    public function deleteVariant(Product $product, string $variantId): bool
    {
        return $this->variantAction->delete($product, $variantId);
    }

    public function addGalleryImage(Product $product, string $mediaId, int $sortOrder = 0): void
    {
        $this->galleryAction->execute($product, $mediaId, $sortOrder);
    }

    public function removeGalleryImage(Product $product, string $mediaId): void
    {
        $this->galleryAction->remove($product, $mediaId);
    }

    public function reorderGallery(Product $product, array $order): void
    {
        $this->galleryAction->reorder($product, $order);
    }

    public function linkCategories(Product $product, array $ids): void
    {
        $this->relationshipsAction->linkCategories($product, $ids);
    }

    public function linkTags(Product $product, array $ids): void
    {
        $this->relationshipsAction->linkTags($product, $ids);
    }

    public function linkRelated(Product $product, array $ids): void
    {
        $this->relationshipsAction->linkRelated($product, $ids);
    }

    public function linkUpsells(Product $product, array $ids): void
    {
        $this->relationshipsAction->linkUpsells($product, $ids);
    }

    public function linkCrossSells(Product $product, array $ids): void
    {
        $this->relationshipsAction->linkCrossSells($product, $ids);
    }
}
