<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Revision extends Model
{
    use HasUuids;

    protected $table = 'revisions';

    public $timestamps = false;

    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'revision_number',
        'data',
        'changes',
        'summary',
        'is_auto',
    ];

    protected $casts = [
        'data' => 'array',
        'changes' => 'array',
        'is_auto' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * Get the revisionable model.
     */
    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get changed attributes.
     */
    public function getChangedAttributes(): array
    {
        return array_keys($this->changes ?? []);
    }

    /**
     * Get old value for attribute.
     */
    public function getOldValue(string $attribute): mixed
    {
        return $this->changes[$attribute]['old'] ?? null;
    }

    /**
     * Get new value for attribute.
     */
    public function getNewValue(string $attribute): mixed
    {
        return $this->changes[$attribute]['new'] ?? null;
    }

    /**
     * Check if attribute was changed.
     */
    public function hasChange(string $attribute): bool
    {
        return isset($this->changes[$attribute]);
    }
}
