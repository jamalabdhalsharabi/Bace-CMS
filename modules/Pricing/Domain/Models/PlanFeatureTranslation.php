<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeatureTranslation extends Model
{
    use HasUuids;

    protected $table = 'plan_feature_translations';

    protected $fillable = ['feature_id', 'locale', 'label', 'tooltip'];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(PlanFeature::class, 'feature_id');
    }
}
