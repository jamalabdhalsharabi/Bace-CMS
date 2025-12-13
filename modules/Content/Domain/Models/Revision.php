<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Revision Model - Tracks version history for content entities.
 *
 * This model stores snapshots of content at different points in time,
 * enabling version history, rollback, and change comparison.
 *
 * @property string $id UUID primary key
 * @property string $revisionable_id UUID of the versioned entity
 * @property string $revisionable_type Polymorphic model type
 * @property string|null $user_id UUID of user who made the change
 * @property int $revision_number Sequential version number
 * @property array $data Complete entity data snapshot
 * @property array|null $changes Field-level change details
 * @property string|null $summary Human-readable change summary
 * @property bool $is_auto Whether revision was auto-generated
 * @property \Carbon\Carbon $created_at Revision creation timestamp
 *
 * @property-read Model $revisionable The versioned entity (polymorphic)
 * @property-read \App\Models\User|null $user User who created this revision
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Revision forModel(string $type, string $id) Filter by entity
 * @method static \Illuminate\Database\Eloquent\Builder|Revision manual() Filter manual revisions only
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision query()
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

    /**
     * Get the versioned entity.
     *
     * @return MorphTo<Model, Revision>
     */
    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this revision.
     *
     * @return BelongsTo<\App\Models\User, Revision>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get change details for a specific field.
     *
     * @param string $field The field name to check
     * @return array|null Change array with 'old' and 'new' values, or null
     */
    public function getFieldChange(string $field): ?array
    {
        return $this->changes[$field] ?? null;
    }

    /**
     * Scope to filter revisions for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Revision> $query
     * @param string $type The model class name
     * @param string $id The entity UUID
     * @return \Illuminate\Database\Eloquent\Builder<Revision>
     */
    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('revisionable_type', $type)->where('revisionable_id', $id);
    }

    /**
     * Scope to filter only manual (non-auto) revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Revision> $query
     * @return \Illuminate\Database\Eloquent\Builder<Revision>
     */
    public function scopeManual($query)
    {
        return $query->where('is_auto', false);
    }
}
