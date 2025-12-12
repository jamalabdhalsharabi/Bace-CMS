<?php

declare(strict_types=1);

namespace Modules\Pricing\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Pricing\Domain\Models\Coupon;

interface CouponServiceContract
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?Coupon;
    public function findByCode(string $code): ?Coupon;
    public function create(array $data): Coupon;
    public function update(Coupon $coupon, array $data): Coupon;
    public function delete(Coupon $coupon): bool;
    public function activate(Coupon $coupon): Coupon;
    public function deactivate(Coupon $coupon): Coupon;
    public function validate(string $code, string $userId, string $planId): array;
    public function apply(string $code, string $userId, string $subscriptionId): array;
}
