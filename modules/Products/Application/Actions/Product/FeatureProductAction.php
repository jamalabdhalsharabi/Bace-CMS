<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

final class FeatureProductAction extends Action
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    public function execute(Product $product): Product
    {
        $this->repository->update($product->id, ['is_featured' => true]);

        return $product->fresh();
    }

    public function unfeature(Product $product): Product
    {
        $this->repository->update($product->id, ['is_featured' => false]);

        return $product->fresh();
    }
}
