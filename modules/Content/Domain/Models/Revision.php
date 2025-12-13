<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Revision
 *
 * Eloquent model representing a content revision
 * for version history and change tracking.
 *
 * @package Modules\Content\Domain\Models
 *
 * @property string $id
 * @property string $revisionable_id
 * @property string $revisionable_type
 * @property string|null $user_id
 * @property int $revision_number
 * @property array $data
 * @property array|null $changes
 * @property string|null $summary
 * @property bool $is_auto
 * @property \Carbon\Carbon $created_at
 *
 * @property-read Model $revisionable
 * @property-read \Modules\Users\Domain\Models\User|null $user
 */
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
