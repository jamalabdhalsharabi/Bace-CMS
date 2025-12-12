<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Model
{
    use HasUuids;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'bio',
        'locale',
        'timezone',
        'date_format',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        return Storage::disk(config('users.avatars.disk', 'public'))->url($this->avatar);
    }

    /**
     * Get initials for avatar placeholder.
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);

        return mb_strtoupper($first . $last) ?: 'U';
    }

    /**
     * Get locale or default.
     */
    public function getLocaleOrDefaultAttribute(): string
    {
        return $this->locale ?? config('app.locale', 'en');
    }

    /**
     * Get timezone or default.
     */
    public function getTimezoneOrDefaultAttribute(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Set meta value.
     */
    public function setMeta(string $key, mixed $value): self
    {
        $meta = $this->meta ?? [];
        $meta[$key] = $value;
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }
}
