<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserBan Model - Tracks user bans and suspensions.
 *
 * This model stores ban records for users, including temporary
 * and permanent bans with lifting capability.
 *
 * @property string $id UUID primary key
 * @property string $user_id Foreign key to banned user
 * @property string|null $reason Ban reason
 * @property \Carbon\Carbon|null $banned_until Ban expiration (null = permanent)
 * @property string $banned_by UUID of banning admin
 * @property \Carbon\Carbon|null $lifted_at When ban was lifted early
 * @property string|null $lifted_by UUID of admin who lifted ban
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read User $user The banned user
 * @property-read User $banner The admin who issued the ban
 * @property-read User|null $lifter The admin who lifted the ban
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserBan active() Filter active bans
 * @method static \Illuminate\Database\Eloquent\Builder|UserBan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBan query()
 */
class UserBan extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'user_bans';

    protected $fillable = [
        'user_id',
        'reason',
        'banned_until',
        'banned_by',
        'lifted_at',
        'lifted_by',
        'created_at',
    ];

    protected $casts = [
        'banned_until' => 'datetime',
        'lifted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the banned user.
     *
     * @return BelongsTo<User, UserBan>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who issued the ban.
     *
     * @return BelongsTo<User, UserBan>
     */
    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Get the admin who lifted the ban.
     *
     * @return BelongsTo<User, UserBan>
     */
    public function lifter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    /**
     * Check if the ban is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->lifted_at) {
            return false;
        }

        if ($this->banned_until === null) {
            return true; // Permanent ban
        }

        return $this->banned_until->isFuture();
    }

    /**
     * Check if this is a permanent ban.
     *
     * @return bool
     */
    public function isPermanent(): bool
    {
        return $this->banned_until === null;
    }

    /**
     * Lift the ban.
     *
     * @param string|null $lifterId UUID of admin lifting the ban
     * @return self
     */
    public function lift(?string $lifterId = null): self
    {
        $this->update([
            'lifted_at' => now(),
            'lifted_by' => $lifterId ?? auth()->id(),
        ]);
        return $this;
    }

    /**
     * Scope to filter active bans.
     *
     * @param \Illuminate\Database\Eloquent\Builder<UserBan> $query
     * @return \Illuminate\Database\Eloquent\Builder<UserBan>
     */
    public function scopeActive($query)
    {
        return $query->whereNull('lifted_at')
            ->where(function ($q) {
                $q->whereNull('banned_until')
                  ->orWhere('banned_until', '>', now());
            });
    }
}
