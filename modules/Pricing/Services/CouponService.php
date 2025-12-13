<?php

declare(strict_types=1);

namespace Modules\Pricing\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Pricing\Contracts\CouponServiceContract;
use Modules\Pricing\Domain\Models\Coupon;

/**
 * Class CouponService
 *
 * Service class for managing discount coupons including
 * CRUD, validation, and application to subscriptions.
 *
 * @package Modules\Pricing\Services
 */
class CouponService implements CouponServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Coupon::query();
        if (isset($filters['is_active'])) $query->where('is_active', $filters['is_active']);
        if (!empty($filters['code'])) $query->where('code', 'like', "%{$filters['code']}%");
        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Coupon
    {
        return Coupon::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::findByCode($code);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Coupon
    {
        return Coupon::create([
            'code' => strtoupper($data['code']),
            'type' => $data['type'] ?? 'percentage',
            'value' => $data['value'],
            'applies_to_plans' => $data['applies_to']['plans'] ?? null,
            'applies_to_periods' => $data['applies_to']['billing_periods'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_user_limit' => $data['per_user_limit'] ?? 1,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'first_payment_only' => $data['first_payment_only'] ?? true,
            'is_active' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update(array_filter($data, fn($v) => $v !== null));
        return $coupon->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Coupon $coupon): bool
    {
        return $coupon->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Coupon $coupon): Coupon
    {
        $coupon->update(['is_active' => true]);
        return $coupon->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Coupon $coupon): Coupon
    {
        $coupon->update(['is_active' => false]);
        return $coupon->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(string $code, string $userId, string $planId): array
    {
        $coupon = $this->findByCode($code);
        
        if (!$coupon) {
            return ['valid' => false, 'error' => 'Coupon not found'];
        }
        
        if (!$coupon->isValid()) {
            return ['valid' => false, 'error' => 'Coupon is expired or inactive'];
        }
        
        if (!$coupon->canBeUsedBy($userId)) {
            return ['valid' => false, 'error' => 'Usage limit reached'];
        }
        
        if (!$coupon->appliesToPlan($planId)) {
            return ['valid' => false, 'error' => 'Coupon does not apply to this plan'];
        }
        
        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_type' => $coupon->type,
            'discount_value' => $coupon->value,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(string $code, string $userId, string $subscriptionId): array
    {
        $coupon = $this->findByCode($code);
        
        if (!$coupon || !$coupon->canBeUsedBy($userId)) {
            return ['success' => false, 'error' => 'Invalid coupon'];
        }
        
        $usage = $coupon->recordUsage($userId, $subscriptionId);
        
        return [
            'success' => true,
            'usage_id' => $usage->id,
            'discount_applied' => true,
        ];
    }
}
