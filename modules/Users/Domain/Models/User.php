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
     * Get user profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get full name from profile.
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
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile?->avatar_url;
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Activate user.
     */
    public function activate(): self
    {
        $this->update(['status' => 'active']);

        return $this;
    }

    /**
     * Suspend user.
     */
    public function suspend(): self
    {
        $this->update(['status' => 'suspended']);

        return $this;
    }

    /**
     * Record login.
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Scope: Active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Verified users only.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope: Search by email or name.
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
