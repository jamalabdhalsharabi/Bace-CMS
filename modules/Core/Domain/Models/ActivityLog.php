<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ActivityLog Model - Records user actions and system events.
 *
 * This model provides an audit trail of user activities,
 * system events, and changes made throughout the application.
 *
 * @property string $id UUID primary key
 * @property string|null $user_id UUID of user who performed the action
 * @property string $action Action type (e.g., 'created', 'updated', 'deleted')
 * @property string|null $subject_type Polymorphic model type of affected entity
 * @property string|null $subject_id UUID of affected entity
 * @property string|null $description Human-readable action description
 * @property array|null $properties Additional context data as JSON
 * @property string|null $ip_address Client IP address
 * @property string|null $user_agent Client browser user agent
 * @property \Carbon\Carbon $created_at Log entry timestamp
 *
 * @property-read \App\Models\User|null $user User who performed the action
 * @property-read Model|null $subject The affected entity (polymorphic)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog forUser(string $userId) Filter by user
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog action(string $action) Filter by action type
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog forSubject(Model $subject) Filter by entity
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog query()
 */
class ActivityLog extends Model
{
    use HasUuids;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
            $model->ip_address = $model->ip_address ?? request()->ip();
            $model->user_agent = $model->user_agent ?? request()->userAgent();
        });
    }

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo<\App\Models\User, ActivityLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the affected entity.
     *
     * @return MorphTo<Model, ActivityLog>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new activity log entry.
     *
     * @param string $action The action type
     * @param Model|null $subject The affected entity
     * @param string|null $description Human-readable description
     * @param array<string, mixed> $properties Additional context
     * @return self The created log entry
     */
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    /**
     * Scope to filter logs by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @param string $userId The user UUID
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter logs by action type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @param string $action The action type
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter logs for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @param Model $subject The entity to filter by
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeForSubject($query, Model $subject)
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }
}
