<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PricingPlan
 *
 * Eloquent model representing a pricing plan with translations,
 * prices, features, limits, and subscriptions.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $slug
 * @property string $type
 * @property int $trial_days
 * @property string $status
 * @property bool $is_recommended
 * @property bool $is_default
 * @property int $sort_order
 * @property array $billing_periods
 * @property array|null $meta
 * @property string|null $created_by
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|PricingPlanTranslation[] $translations
 * @property-read PricingPlanTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|PlanPrice[] $prices
 * @property-read \Illuminate\Database\Eloquent\Collection|PlanFeature[] $features
 * @property-read \Illuminate\Database\Eloquent\Collection|PlanLimit[] $limits
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscription[] $subscriptions
 * @property-read string|null $name
 * @property-read string|null $description
 */
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

    /**
     * Define the has-many relationship with plan translations.
     *
     * Retrieves all translation records for this plan across
     * all supported locales including name, description, and CTA text.
     *
     * @return HasMany The has-many relationship instance to PricingPlanTranslation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PricingPlanTranslation::class, 'plan_id');
    }

    /**
     * Define the has-one relationship with the current locale translation.
     *
     * Retrieves the translation record matching the application's
     * current locale setting for displaying localized plan content.
     *
     * @return HasOne The has-one relationship instance to PricingPlanTranslation
     */
    public function translation(): HasOne
    {
        return $this->hasOne(PricingPlanTranslation::class, 'plan_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Define the has-many relationship with plan prices.
     *
     * Retrieves all price configurations for this plan across
     * different currencies and billing periods.
     *
     * @return HasMany The has-many relationship instance to PlanPrice
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class, 'plan_id');
    }

    /**
     * Define the has-many relationship with plan features.
     *
     * Retrieves all features included in this plan ordered by
     * their sort_order for consistent display.
     *
     * @return HasMany The has-many relationship instance to PlanFeature
     */
    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id')->orderBy('sort_order');
    }

    /**
     * Define the has-many relationship with plan usage limits.
     *
     * Retrieves all resource limits defined for this plan
     * such as storage, API calls, or users.
     *
     * @return HasMany The has-many relationship instance to PlanLimit
     */
    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class, 'plan_id');
    }

    /**
     * Define the has-many relationship with active subscriptions.
     *
     * Retrieves all subscription records for users subscribed
     * to this pricing plan.
     *
     * @return HasMany The has-many relationship instance to Subscription
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Define the has-many relationship with plan links.
     *
     * Retrieves all polymorphic links connecting this plan
     * to products, services, or other entities.
     *
     * @return HasMany The has-many relationship instance to PlanLink
     */
    public function links(): HasMany
    {
        return $this->hasMany(PlanLink::class, 'plan_id');
    }

    /**
     * Get products linked to this pricing plan.
     *
     * Filters plan links to only return those linked to
     * Product models for product-based subscriptions.
     *
     * @return HasMany The filtered has-many relationship for products
     */
    public function linkedProducts(): HasMany
    {
        return $this->links()->where('linkable_type', 'Modules\\Products\\Domain\\Models\\Product');
    }

    /**
     * Get services linked to this pricing plan.
     *
     * Filters plan links to only return those linked to
     * Service models for service-based subscriptions.
     *
     * @return HasMany The filtered has-many relationship for services
     */
    public function linkedServices(): HasMany
    {
        return $this->links()->where('linkable_type', 'Modules\\Services\\Domain\\Models\\Service');
    }

    /**
     * Accessor for the plan's localized name.
     *
     * Returns the name from the current locale translation if available,
     * otherwise falls back to the first available translation.
     *
     * @return string|null The localized plan name or null if no translations
     */
    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name;
    }

    /**
     * Accessor for the plan's localized description.
     *
     * Returns the description from the current locale translation
     * for displaying plan details on pricing pages.
     *
     * @return string|null The localized description or null if not set
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    /**
     * Get the price for a specific currency and billing period.
     *
     * Searches for a price record matching the given currency ID
     * and billing period (monthly, yearly, etc.).
     *
     * @param string $currencyId The UUID of the currency
     * @param string $period The billing period (default: 'monthly')
     *
     * @return PlanPrice|null The matching price or null if not found
     */
    public function getPriceForCurrency(string $currencyId, string $period = 'monthly'): ?PlanPrice
    {
        return $this->prices()
            ->where('currency_id', $currencyId)
            ->where('billing_period', $period)
            ->first();
    }

    /**
     * Get the limit value for a specific resource.
     *
     * Retrieves the configured limit for resources like storage,
     * API calls, or user seats. Returns null if no limit is set.
     *
     * @param string $resource The resource identifier to look up
     *
     * @return int|null The limit value or null if unlimited/not set
     */
    public function getLimit(string $resource): ?int
    {
        return $this->limits()->where('resource', $resource)->value('limit_value');
    }

    /**
     * Check if the plan includes a specific feature.
     *
     * Determines whether a feature with the given key exists
     * in this plan's feature list.
     *
     * @param string $key The feature key to check for
     *
     * @return bool True if the feature is included, false otherwise
     */
    public function hasFeature(string $key): bool
    {
        return $this->features()->where('feature_key', $key)->exists();
    }

    /**
     * Query scope to filter only active plans.
     *
     * Filters plans with 'active' status for public display.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query
     */
    public function scopeActive($query) { return $query->where('status', 'active'); }

    /**
     * Query scope to filter only recommended plans.
     *
     * Filters plans marked as recommended for highlighting.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query
     */
    public function scopeRecommended($query) { return $query->where('is_recommended', true); }

    /**
     * Query scope to order plans by sort order.
     *
     * Orders plans by their sort_order field for consistent display.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query
     */
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
}
