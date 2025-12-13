<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasTranslations;

/**
 * Class PlanFeature
 *
 * Eloquent model representing a pricing plan feature
 * with translations and type-specific behavior.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $plan_id
 * @property string $feature_key
 * @property string|null $value
 * @property string $type
 * @property bool $is_highlighted
 * @property int $sort_order
 *
 * @property-read PricingPlan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|PlanFeatureTranslation[] $translations
 * @property-read PlanFeatureTranslation|null $translation
 * @property-read string|null $label
 */
class PlanFeature extends Model
{
    use HasUuids;
    use HasTranslations;

    public array $translatedAttributes = ['label', 'description'];
    public string $translationForeignKey = 'feature_id';

    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id', 'feature_key', 'value', 'type', 'is_highlighted', 'sort_order',
    ];

    protected $casts = [
        'is_highlighted' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent pricing plan.
     *
     * @return BelongsTo<PricingPlan, PlanFeature>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    /**
     * Check if this is a boolean feature (included/not included).
     *
     * @return bool True if feature type is boolean
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Check if this is a limit feature (e.g., "10 users").
     *
     * @return bool True if feature type is limit
     */
    public function isLimit(): bool
    {
        return $this->type === 'limit';
    }

    /**
     * Check if this is a text feature (custom description).
     *
     * @return bool True if feature type is text
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }
}
