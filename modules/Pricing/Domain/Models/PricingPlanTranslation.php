<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PricingPlanTranslation
 *
 * Eloquent model representing a pricing plan translation
 * for multi-language support.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $plan_id
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property string|null $short_description
 * @property string|null $cta_text
 * @property string|null $badge_text
 *
 * @property-read PricingPlan $plan
 */
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
