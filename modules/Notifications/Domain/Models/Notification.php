<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Notification Model - Represents user notifications.
 *
 * This model handles in-app notifications with read status,
 * polymorphic entity linking, and structured data payload.
 *
 * @property string $id UUID primary key
 * @property string $user_id Foreign key to user
 * @property string $type Notification type identifier
 * @property string|null $notifiable_id UUID of related entity
 * @property string|null $notifiable_type Polymorphic model type
 * @property array $data Notification payload as JSON
 * @property \Carbon\Carbon|null $read_at When notification was read
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \App\Models\User $user Notification recipient
 * @property-read Model|null $notifiable Related entity (polymorphic)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Notification unread() Filter unread notifications
 * @method static \Illuminate\Database\Eloquent\Builder|Notification read() Filter read notifications
 * @method static \Illuminate\Database\Eloquent\Builder|Notification forUser(string $userId) Filter by user
 * @method static \Illuminate\Database\Eloquent\Builder|Notification ofType(string $type) Filter by type
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 */
class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the notification recipient.
     *
     * @return BelongsTo<\App\Models\User, Notification>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the related entity.
     *
     * @return MorphTo<Model, Notification>
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     *
     * @return self Returns self for method chaining
     */
    public function markAsRead(): self
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    /**
     * Mark the notification as unread.
     *
     * @return self Returns self for method chaining
     */
    public function markAsUnread(): self
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    /**
     * Check if notification has been read.
     *
     * @return bool True if read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread.
     *
     * @return bool True if unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Get the notification title from data.
     *
     * @return string The title
     */
    public function getTitle(): string
    {
        return $this->data['title'] ?? '';
    }

    /**
     * Get the notification message from data.
     *
     * @return string The message
     */
    public function getMessage(): string
    {
        return $this->data['message'] ?? '';
    }

    /**
     * Get the notification icon from data.
     *
     * @return string|null The icon class or null
     */
    public function getIcon(): ?string
    {
        return $this->data['icon'] ?? null;
    }

    /**
     * Get the notification URL from data.
     *
     * @return string|null The URL or null
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }

    /**
     * Scope to filter unread notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Notification> $query
     * @return \Illuminate\Database\Eloquent\Builder<Notification>
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to filter read notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Notification> $query
     * @return \Illuminate\Database\Eloquent\Builder<Notification>
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope to filter notifications by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Notification> $query
     * @param string $userId The user UUID
     * @return \Illuminate\Database\Eloquent\Builder<Notification>
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter notifications by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Notification> $query
     * @param string $type The notification type
     * @return \Illuminate\Database\Eloquent\Builder<Notification>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
