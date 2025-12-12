<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingPlanTranslation extends Model
{
    use HasUuids;

    protected $table = 'pricing_plan_translations';

    protected $fillable = [
        'plan_id', 'locale', 'name', 'description', 'short_description',
        'cta_text', 'badge_text',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }
}
