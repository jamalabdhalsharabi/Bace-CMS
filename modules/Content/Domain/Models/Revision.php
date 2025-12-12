<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

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
        'revisionable_id',
        'revisionable_type',
        'user_id',
        'revision_number',
        'data',
        'changes',
        'summary',
        'is_auto',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'changes' => 'array',
        'is_auto' => 'boolean',
        'revision_number' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function getFieldChange(string $field): ?array
    {
        return $this->changes[$field] ?? null;
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('revisionable_type', $type)->where('revisionable_id', $id);
    }

    public function scopeManual($query)
    {
        return $query->where('is_auto', false);
    }
}
