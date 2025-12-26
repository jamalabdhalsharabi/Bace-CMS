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
 * @property string $created_by UUID of user who made the change
 * @property int $version Sequential version number
 * @property string $type Revision type (create, update, publish, unpublish, restore)
 * @property array|null $old_data Previous entity data snapshot
 * @property array $new_data Current entity data snapshot
 * @property array|null $diff Field-level change details
 * @property string|null $summary Human-readable change summary
 * @property \Carbon\Carbon $created_at Revision creation timestamp
 *
 * @property-read Model $revisionable The versioned entity (polymorphic)
 * @property-read \App\Models\User $creator User who created this revision
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
        'created_by',
        'version',
        'type',
        'old_data',
        'new_data',
        'diff',
        'summary',
        'created_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'diff' => 'array',
        'version' => 'integer',
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get change details for a specific field.
     *
     * @param string $field The field name to check
     * @return array|null Change array with 'old' and 'new' values, or null
     */
    public function getFieldChange(string $field): ?array
    {
        return $this->diff[$field] ?? null;
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
     * Scope to filter revisions by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Revision> $query
     * @param string $type The revision type to filter by
     * @return \Illuminate\Database\Eloquent\Builder<Revision>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
