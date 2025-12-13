<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\Traits\HasRoles;

/**
 * User Model - Represents authenticated users in the system.
 *
 * This model handles user authentication, authorization, and profile management.
 * It supports two-factor authentication, soft deletes, and role-based permissions.
 *
 * @property string $id UUID primary key
 * @property string $email User's unique email address
 * @property \Carbon\Carbon|null $email_verified_at Email verification timestamp
 * @property string $password Hashed password
 * @property string $name Display name
 * @property string|null $avatar_id Foreign key to media table for user avatar
 * @property string $status Account status (active, inactive, banned, pending)
 * @property bool $two_factor_enabled Whether 2FA is enabled
 * @property string|null $two_factor_secret Encrypted 2FA secret key
 * @property \Carbon\Carbon|null $password_changed_at Last password change timestamp
 * @property bool $must_change_password Force password change on next login
 * @property string|null $locale User's preferred locale (e.g., 'en', 'ar')
 * @property string|null $timezone User's timezone (e.g., 'UTC', 'Asia/Riyadh')
 * @property \Carbon\Carbon|null $last_login_at Last successful login timestamp
 * @property string|null $last_login_ip IP address of last login
 * @property \Carbon\Carbon|null $last_active_at Last activity timestamp
 * @property string|null $remember_token Remember me token for persistent sessions
 * @property array|null $meta Additional metadata as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read \Modules\Media\Domain\Models\Media|null $avatar User's avatar image
 * @property-read UserProfile|null $profile User's extended profile information
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserSession> $sessions Active user sessions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserSetting> $settings User-specific settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Auth\Domain\Models\Role> $roles Assigned roles
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User active() Filter only active users
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 */
class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    protected $fillable = [
        'email',
        'password',
        'name',
        'avatar_id',
        'status',
        'two_factor_enabled',
        'two_factor_secret',
        'password_changed_at',
        'must_change_password',
        'locale',
        'timezone',
        'last_login_at',
        'last_login_ip',
        'last_active_at',
        'meta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get the user's avatar image.
     *
     * @return BelongsTo<\Modules\Media\Domain\Models\Media, User>
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'avatar_id');
    }

    /**
     * Get the user's extended profile information.
     *
     * @return HasOne<UserProfile>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get all active sessions for this user.
     *
     * @return HasMany<UserSession>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get all user-specific settings.
     *
     * @return HasMany<UserSetting>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Scope to filter only active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder<User> $query
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the user account is active.
     *
     * @return bool True if status is 'active'
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user account is banned.
     *
     * @return bool True if status is 'banned'
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * Get a specific user setting value.
     *
     * @param string $key The setting key to retrieve
     * @param mixed $default Default value if setting not found
     * @return mixed The setting value or default
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings()->where('key', $key)->value('value') ?? $default;
    }
}
