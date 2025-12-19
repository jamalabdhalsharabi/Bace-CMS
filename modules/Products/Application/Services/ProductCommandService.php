<?php

declare(strict_types=1);

namespace Modules\Products\Application\Services;

use Modules\Products\Application\Actions\Product\CreateProductAction;
use Modules\Products\Application\Actions\Product\DeleteProductAction;
use Modules\Products\Application\Actions\Product\DuplicateProductAction;
use Modules\Products\Application\Actions\Product\FeatureProductAction;
use Modules\Products\Application\Actions\Product\PublishProductAction;
use Modules\Products\Application\Actions\Product\UpdateProductAction;
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
     *
     * @param CreateProductAction $createAction Action for creating products
     * @param UpdateProductAction $updateAction Action for updating products
     * @param PublishProductAction $publishAction Action for publishing products
     * @param DeleteProductAction $deleteAction Action for deleting products
     * @param DuplicateProductAction $duplicateAction Action for duplicating products
     * @param FeatureProductAction $featureAction Action for featuring products
     */
    public function __construct(
        private readonly CreateProductAction $createAction,
        private readonly UpdateProductAction $updateAction,
        private readonly PublishProductAction $publishAction,
        private readonly DeleteProductAction $deleteAction,
        private readonly DuplicateProductAction $duplicateAction,
        private readonly FeatureProductAction $featureAction,
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
}
