<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\CouponServiceContract;
use Modules\Pricing\Http\Resources\CouponResource;

class CouponController extends BaseController
{
    public function __construct(protected CouponServiceContract $couponService) {}

    public function index(Request $request): JsonResponse
    {
        $coupons = $this->couponService->list($request->only(['is_active', 'code']), $request->integer('per_page', 20));
        return $this->paginated(CouponResource::collection($coupons)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        return $coupon ? $this->success(new CouponResource($coupon)) : $this->notFound('Coupon not found');
    }

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

    public function update(Request $request, string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->update($coupon, $request->all())));
    }

    public function destroy(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        $this->couponService->delete($coupon);
        return $this->success(null, 'Coupon deleted');
    }

    public function activate(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->activate($coupon)));
    }

    public function deactivate(string $id): JsonResponse
    {
        $coupon = $this->couponService->find($id);
        if (!$coupon) return $this->notFound('Coupon not found');
        
        return $this->success(new CouponResource($this->couponService->deactivate($coupon)));
    }

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
