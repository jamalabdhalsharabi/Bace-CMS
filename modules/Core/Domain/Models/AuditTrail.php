<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditTrail Model - Tracks entity changes for compliance.
 *
 * This model stores detailed audit records of all entity changes
 * including who, what, when, and the actual data changes.
 *
 * @property string $id UUID primary key
 * @property string $entity_type Type of entity being audited
 * @property string $entity_id UUID of the entity
 * @property string $action Action performed (create, update, delete, etc.)
 * @property string|null $user_id Foreign key to users
 * @property string|null $user_email User email at time of action
 * @property string|null $user_name User name at time of action
 * @property array|null $changes Changed data (old and new values)
 * @property string|null $ip_address User's IP address
 * @property string|null $user_agent Browser user agent
 * @property string|null $url Request URL
 * @property string|null $method HTTP method
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read \App\Models\User|null $user The user who performed the action
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail forEntity(string $type, string $id) Filter by entity
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail query()
 */
class AuditTrail extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'audit_trails';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'user_id',
        'user_email',
        'user_name',
        'changes',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<\App\Models\User, AuditTrail>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Scope to filter by entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditTrail> $query
     * @param string $type Entity type
     * @param string $id Entity UUID
     * @return \Illuminate\Database\Eloquent\Builder<AuditTrail>
     */
    public function scopeForEntity($query, string $type, string $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }
}
