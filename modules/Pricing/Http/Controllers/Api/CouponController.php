<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\CouponServiceContract;
use Modules\Pricing\Http\Requests\StoreCouponRequest;
use Modules\Pricing\Http\Requests\ValidateCouponRequest;
use Modules\Pricing\Http\Resources\CouponResource;

/**
 * Class CouponController
 *
 * API controller for managing discount coupons including
 * CRUD operations and coupon validation.
 *
 * @package Modules\Pricing\Http\Controllers\Api
 */
class CouponController extends BaseController
{
    /**
     * The coupon service instance.
     *
     * @var CouponServiceContract
     */
    protected CouponServiceContract $couponService;

    /**
     * Create a new CouponController instance.
     *
     * @param CouponServiceContract $couponService The coupon service implementation
     */
    public function __construct(CouponServiceContract $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Display a paginated listing of coupons.
     *
     * @param Request $request The request with optional filters
     * @return JsonResponse Paginated list of coupons
     */
    public function index(Request $request): JsonResponse
    {
        $coupons = $this->couponService->list($request->only(['is_active', 'code']), $request->integer('per_page', 20));
        return $this->paginated(CouponResource::collection($coupons)->resource);
    }

    /**
     * Display the specified coupon by its UUID.
     *
     * @param string $id The UUID of the coupon
     * @return JsonResponse The coupon data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        return $coupon ? $this->success(new CouponResource($coupon)) : $this->notFound('Coupon not found');
    }

    /**
     * Store a newly created coupon.
     */
    public function store(StoreCouponRequest $request): JsonResponse
    {
        return $this->created(new CouponResource($this->couponService->create($request->validated())));
    }

    /**
     * Update the specified coupon.
     *
     * @param Request $request The request containing updated data
     * @param string $id The UUID of the coupon
     * @return JsonResponse The updated coupon or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->update($coupon, $request->all())));
    }

    /**
     * Delete the specified coupon.
     *
     * @param string $id The UUID of the coupon
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        $this->couponService->delete($coupon);
        return $this->success(null, 'Coupon deleted');
    }

    /**
     * Activate a coupon.
     *
     * @param string $id The UUID of the coupon
     * @return JsonResponse The activated coupon or 404 error
     */
    public function activate(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->activate($coupon)));
    }

    /**
     * Deactivate a coupon.
     *
     * @param string $id The UUID of the coupon
     * @return JsonResponse The deactivated coupon or 404 error
     */
    public function deactivate(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->deactivate($coupon)));
    }

    /**
     * Validate a coupon code for a specific plan.
     */
    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->couponService->validate($data['code'], request()->user()?->id, $data['plan_id']);
        
        if (!$result['valid']) {
            return $this->error($result['error'], 422);
        }
        
        return $this->success([
            'valid' => true,
            'discount_type' => $result['discount_type'],
            'discount_value' => $result['discount_value'],
        ]);
    }
}
