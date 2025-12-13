<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class BaseModel
 *
 * Abstract base model providing common functionality
 * for all domain models including UUIDs, soft deletes, and scopes.
 *
 * @package Modules\Core\Domain\Models
 *
 * @property string $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read string $created_at_human
 * @property-read string $updated_at_human
 */
abstract class BaseModel extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Scope: Active records only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by position/ordering.
     */
    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('ordering', $direction);
    }

    /**
     * Scope: Recently created.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    /**
     * Check if model is active.
     */
    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    /**
     * Get human-readable created date.
     */
    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    /**
     * Get human-readable updated date.
     */
    public function getUpdatedAtHumanAttribute(): string
    {
        return $this->updated_at?->diffForHumans() ?? '';
    }
}
