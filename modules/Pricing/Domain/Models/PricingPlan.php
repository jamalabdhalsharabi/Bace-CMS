<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'pricing_plans';

    protected $fillable = [
        'slug', 'type', 'trial_days', 'status', 'is_recommended', 'is_default',
        'sort_order', 'billing_periods', 'meta', 'created_by',
    ];

    protected $casts = [
        'trial_days' => 'integer',
        'is_recommended' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'billing_periods' => 'array',
        'meta' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PricingPlanTranslation::class, 'plan_id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PricingPlanTranslation::class, 'plan_id')
            ->where('locale', app()->getLocale());
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class, 'plan_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id')->orderBy('sort_order');
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(PlanLink::class, 'plan_id');
    }

    public function linkedProducts(): HasMany
    {
        return $this->links()->where('linkable_type', 'Modules\\Products\\Domain\\Models\\Product');
    }

    public function linkedServices(): HasMany
    {
        return $this->links()->where('linkable_type', 'Modules\\Services\\Domain\\Models\\Service');
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    public function getPriceForCurrency(string $currencyId, string $period = 'monthly'): ?PlanPrice
    {
        return $this->prices()
            ->where('currency_id', $currencyId)
            ->where('billing_period', $period)
            ->first();
    }

    public function getLimit(string $resource): ?int
    {
        return $this->limits()->where('resource', $resource)->value('limit_value');
    }

    public function hasFeature(string $key): bool
    {
        return $this->features()->where('feature_key', $key)->exists();
    }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeRecommended($query) { return $query->where('is_recommended', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
}
