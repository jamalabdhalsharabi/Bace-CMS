<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class PlanLink
 *
 * Eloquent model representing a link between a pricing plan
 * and a product or service entity.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $plan_id
 * @property string $linkable_type
 * @property string $linkable_id
 * @property bool $is_required
 * @property array|null $meta
 *
 * @property-read PricingPlan $plan
 * @property-read Model $linkable
 */
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
