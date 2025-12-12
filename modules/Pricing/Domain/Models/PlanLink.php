<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanLink extends Model
{
    use HasUuids;

    protected $table = 'plan_links';

    protected $fillable = ['plan_id', 'linkable_type', 'linkable_id', 'is_required', 'meta'];

    protected $casts = [
        'is_required' => 'boolean',
        'meta' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
