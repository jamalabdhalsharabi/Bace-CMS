<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
