<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlanFeature extends Model
{
    use HasUuids;

    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id', 'feature_key', 'value', 'type', 'is_highlighted', 'sort_order',
    ];

    protected $casts = [
        'is_highlighted' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PlanFeatureTranslation::class, 'feature_id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PlanFeatureTranslation::class, 'feature_id')
            ->where('locale', app()->getLocale());
    }

    public function getLabelAttribute(): ?string
    {
        return $this->translation?->label ?? $this->translations->first()?->label;
    }

    public function isBoolean(): bool { return $this->type === 'boolean'; }
    public function isLimit(): bool { return $this->type === 'limit'; }
    public function isText(): bool { return $this->type === 'text'; }
}
