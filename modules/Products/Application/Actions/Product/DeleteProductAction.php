<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Product;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

final class DeleteProductAction extends Action
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    public function execute(Product $product): bool
    {
        $product->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($product->id);
    }

    public function forceDelete(string $id): bool
    {
        $product = Product::withTrashed()->find($id);

        if ($product) {
            $product->translations()->delete();
            $product->categories()->detach();
            $product->variants()->delete();
            $product->prices()->delete();

            return $product->forceDelete();
        }

        return false;
    }

    public function restore(string $id): ?Product
    {
        $product = Product::withTrashed()->find($id);

        if ($product) {
            $product->restore();
            return $product->fresh();
        }

        return null;
    }
}
