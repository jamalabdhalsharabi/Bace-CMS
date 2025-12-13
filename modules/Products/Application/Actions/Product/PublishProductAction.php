<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Events\ProductPublished;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Publish Product Action.
 */
final class PublishProductAction extends Action
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    public function execute(Product $product): Product
    {
        $this->repository->update($product->id, [
            'status' => 'published',
            'published_at' => $product->published_at ?? now(),
        ]);

        event(new ProductPublished($product));

        return $product->fresh();
    }

    public function unpublish(Product $product): Product
    {
        $this->repository->update($product->id, [
            'status' => 'draft',
        ]);

        return $product->fresh();
    }

    public function archive(Product $product): Product
    {
        $this->repository->update($product->id, [
            'status' => 'archived',
        ]);

        return $product->fresh();
    }
}
