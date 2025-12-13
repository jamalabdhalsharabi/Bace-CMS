<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * Eloquent model representing a system user with authentication,
 * profile, and status management capabilities.
 *
 * @package Modules\Users\Domain\Models
 *
 * @property string $id
 * @property string $email
 * @property string $password
 * @property string $status
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read UserProfile|null $profile
 * @property-read string $full_name
 * @property-read string|null $avatar_url
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'status',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define the one-to-one relationship with the user's profile.
     *
     * Retrieves the associated UserProfile model containing extended
     * user information such as name, phone, avatar, and preferences.
     *
     * @return HasOne The has-one relationship instance to UserProfile
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Accessor for the user's full name derived from their profile.
     *
     * Concatenates the first name and last name from the associated profile.
     * Falls back to the user's email address if no profile exists or if
     * the profile has no name information.
     *
     * @return string The user's full name or email as fallback
     */
    public function getFullNameAttribute(): string
    {
        if (!$this->profile) {
            return $this->email;
        }

        $name = trim($this->profile->first_name . ' ' . $this->profile->last_name);

        return $name ?: $this->email;
    }

    /**
     * Accessor for the user's avatar URL from their profile.
     *
     * Returns the fully qualified URL to the user's avatar image
     * if one has been uploaded, otherwise returns null.
     *
     * @return string|null The avatar URL or null if no avatar exists
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile?->avatar_url;
    }

    /**
     * Determine if the user account is currently active.
     *
     * An active user can log in and access the system normally.
     * This checks if the status field equals 'active'.
     *
     * @return bool True if the user is active, false otherwise
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine if the user account is currently suspended.
     *
     * A suspended user cannot log in or access the system.
     * This checks if the status field equals 'suspended'.
     *
     * @return bool True if the user is suspended, false otherwise
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Activate the user account.
     *
     * Sets the user's status to 'active', allowing them to log in
     * and access the system. This operation is persisted immediately.
     *
     * @return self The current User instance for method chaining
     */
    public function activate(): self
    {
        $this->update(['status' => 'active']);

        return $this;
    }

    /**
     * Suspend the user account.
     *
     * Sets the user's status to 'suspended', preventing them from
     * logging in or accessing the system. This operation is persisted immediately.
     *
     * @return self The current User instance for method chaining
     */
    public function suspend(): self
    {
        $this->update(['status' => 'suspended']);

        return $this;
    }

    /**
     * Record the user's login activity.
     *
     * Updates the last login timestamp and IP address for audit
     * and security tracking purposes. If no IP is provided,
     * the current request IP will be used.
     *
     * @param string|null $ip The IP address to record, or null to use request IP
     *
     * @return void
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Query scope to filter only active users.
     *
     * Adds a WHERE clause to filter users whose status is 'active'.
     * Use this scope to exclude suspended or inactive users from queries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Query scope to filter only email-verified users.
     *
     * Adds a WHERE clause to filter users whose email has been verified.
     * Use this scope to ensure only confirmed users are included.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Query scope to search users by email or profile name.
     *
     * Performs a LIKE search across the user's email address and
     * their profile's first name and last name fields. Useful for
     * implementing user search functionality.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $term The search term to match against email and names
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('email', 'LIKE', "%{$term}%")
                ->orWhereHas('profile', function ($pq) use ($term) {
                    $pq->where('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%");
                });
        });
    }
}
