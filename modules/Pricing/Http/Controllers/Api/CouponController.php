<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\CouponServiceContract;
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
     *
     * @param Request $request The request containing coupon data
     * @return JsonResponse The created coupon (HTTP 201)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'applies_to.plans' => 'nullable|array',
            'applies_to.billing_periods' => 'nullable|array',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'first_payment_only' => 'nullable|boolean',
        ]);
        
        return $this->created(new CouponResource($this->couponService->create($request->all())));
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
     *
     * @param Request $request The request containing code and plan_id
     * @return JsonResponse Validation result with discount info
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'plan_id' => 'required|uuid',
        ]);
        
        $result = $this->couponService->validate($request->code, auth()->id(), $request->plan_id);
        
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
