<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PlanLimit
 *
 * Eloquent model representing a pricing plan resource limit
 * for quota enforcement.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $plan_id
 * @property string $resource
 * @property int|null $limit_value
 * @property string|null $period
 *
 * @property-read PricingPlan $plan
 */
class PlanLimit extends Model
{
    use HasUuids;

    protected $table = 'plan_limits';

    protected $fillable = ['plan_id', 'resource', 'limit_value', 'period'];

    protected $casts = ['limit_value' => 'integer'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function isUnlimited(): bool
    {
        return $this->limit_value === -1 || $this->limit_value === null;
    }
}
