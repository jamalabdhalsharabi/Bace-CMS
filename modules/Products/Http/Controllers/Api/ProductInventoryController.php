<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Products\Application\Services\ProductCommandService;
use Modules\Products\Application\Services\ProductQueryService;
use Modules\Products\Http\Requests\BulkUpdatePricesRequest;
use Modules\Products\Http\Requests\BulkUpdateStockRequest;
use Modules\Products\Http\Requests\SetBackorderSettingsRequest;
use Modules\Products\Http\Requests\SetPriceRequest;
use Modules\Products\Http\Requests\SetSalePriceRequest;
use Modules\Products\Http\Requests\SetStockTrackingRequest;
use Modules\Products\Http\Requests\UpdateStockRequest;
use Modules\Products\Http\Resources\ProductResource;

/**
 * Product Inventory Controller.
 *
 * Handles inventory-related operations for products including stock
 * management, pricing, and backorder settings. This controller follows
 * Single Responsibility Principle by focusing only on inventory operations.
 *
 * @package Modules\Products\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ProductListingController For read operations
 * @see ProductManagementController For CRUD operations
 */
final class ProductInventoryController extends BaseController
{
    /**
     * Create a new ProductInventoryController instance.
     *
     * @param ProductQueryService $queryService Service for product read operations
     * @param ProductCommandService $commandService Service for product write operations
     */
    public function __construct(
        private readonly ProductQueryService $queryService,
        private readonly ProductCommandService $commandService
    ) {}

    /**
     * Update stock quantity.
     *
     * @param UpdateStockRequest $request The validated stock update request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When stock update fails
     */
    public function updateStock(UpdateStockRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->updateStock(
                $product,
                $request->quantity,
                $request->type,
                $request->reason
            );

            return $this->success(new ProductResource($product), 'Stock updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Set product price.
     *
     * @param SetPriceRequest $request The validated price request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When price update fails
     */
    public function setPrice(SetPriceRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->setPrice($product, $request->validated());

            return $this->success(new ProductResource($product), 'Price updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to set price: ' . $e->getMessage());
        }
    }

    /**
     * Set sale price.
     *
     * @param SetSalePriceRequest $request The validated sale price request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When sale price update fails
     */
    public function setSalePrice(SetSalePriceRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->setSalePrice($product, $request->validated());

            return $this->success(new ProductResource($product), 'Sale price set');
        } catch (\Throwable $e) {
            return $this->error('Failed to set sale price: ' . $e->getMessage());
        }
    }

    /**
     * Remove sale price.
     *
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When sale price removal fails
     */
    public function removeSalePrice(string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->removeSalePrice($product);

            return $this->success(new ProductResource($product), 'Sale price removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove sale price: ' . $e->getMessage());
        }
    }

    /**
     * Set stock tracking.
     *
     * @param SetStockTrackingRequest $request The validated stock tracking request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When stock tracking update fails
     */
    public function setStockTracking(SetStockTrackingRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->setStockTracking($product, $request->validated());

            return $this->success(new ProductResource($product), 'Stock tracking updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to set stock tracking: ' . $e->getMessage());
        }
    }

    /**
     * Set backorder settings.
     *
     * @param SetBackorderSettingsRequest $request The validated backorder settings request
     * @param string $id The UUID of the product
     *
     * @return JsonResponse The updated product or 404 error
     *
     * @throws \Throwable When backorder settings update fails
     */
    public function setBackorderSettings(SetBackorderSettingsRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->queryService->find($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            $product = $this->commandService->setBackorderSettings(
                $product,
                $request->validated()['allow_backorders']
            );

            return $this->success(new ProductResource($product), 'Backorder settings updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to set backorder settings: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update prices.
     *
     * @param BulkUpdatePricesRequest $request The validated bulk price update request
     *
     * @return JsonResponse The update count
     *
     * @throws \Throwable When bulk update fails
     */
    public function bulkUpdatePrices(BulkUpdatePricesRequest $request): JsonResponse
    {
        try {
            $count = $this->commandService->bulkUpdatePrices($request->validated());

            return $this->success(['updated' => $count], 'Prices updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to bulk update prices: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update stock.
     *
     * @param BulkUpdateStockRequest $request The validated bulk stock update request
     *
     * @return JsonResponse The update count
     *
     * @throws \Throwable When bulk update fails
     */
    public function bulkUpdateStock(BulkUpdateStockRequest $request): JsonResponse
    {
        try {
            $count = $this->commandService->bulkUpdateStock($request->validated()['updates']);

            return $this->success(['updated' => $count], 'Stock updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to bulk update stock: ' . $e->getMessage());
        }
    }
}
