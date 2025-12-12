<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{
    use HasUuids;

    protected $table = 'subscription_usages';

    protected $fillable = ['subscription_id', 'resource', 'quantity', 'period_start', 'period_end'];

    protected $casts = [
        'quantity' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
