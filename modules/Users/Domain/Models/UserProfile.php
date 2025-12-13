<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Class UserProfile
 *
 * Eloquent model representing a user's profile information
 * including personal details, preferences, and avatar.
 *
 * @package Modules\Users\Domain\Models
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $locale
 * @property string|null $timezone
 * @property string|null $date_format
 * @property array|null $meta
 *
 * @property-read User $user
 * @property-read string $full_name
 * @property-read string|null $avatar_url
 * @property-read string $initials
 */
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
     * Define the belongs-to relationship with the parent user.
     *
     * Retrieves the User model that this profile belongs to.
     * Every profile is associated with exactly one user account.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for the user's full name.
     *
     * Concatenates the first name and last name with a space.
     * Returns an empty string if both names are null or empty.
     *
     * @return string The user's full name or empty string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Accessor for the user's avatar image URL.
     *
     * Generates the fully qualified URL to the avatar image
     * using the configured storage disk. Returns null if no
     * avatar has been uploaded.
     *
     * @return string|null The avatar URL or null if no avatar exists
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        return Storage::disk(config('users.avatars.disk', 'public'))->url($this->avatar);
    }

    /**
     * Accessor for the user's initials for avatar placeholders.
     *
     * Extracts the first letter of the first name and last name
     * to create initials. Returns 'U' as default if no names exist.
     * Useful for generating avatar fallback images.
     *
     * @return string The uppercase initials (e.g., 'JD' for John Doe)
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);

        return mb_strtoupper($first . $last) ?: 'U';
    }

    /**
     * Accessor for the user's preferred locale with fallback.
     *
     * Returns the user's preferred language locale if set,
     * otherwise falls back to the application's default locale.
     *
     * @return string The locale code (e.g., 'en', 'ar', 'fr')
     */
    public function getLocaleOrDefaultAttribute(): string
    {
        return $this->locale ?? config('app.locale', 'en');
    }

    /**
     * Accessor for the user's preferred timezone with fallback.
     *
     * Returns the user's preferred timezone if set,
     * otherwise falls back to the application's default timezone.
     *
     * @return string The timezone identifier (e.g., 'UTC', 'America/New_York')
     */
    public function getTimezoneOrDefaultAttribute(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Set a value in the profile's meta data array.
     *
     * Stores arbitrary key-value data in the meta JSON column.
     * Useful for storing custom user preferences or settings
     * without modifying the database schema.
     *
     * @param string $key The meta key to set
     * @param mixed $value The value to store
     *
     * @return self The current UserProfile instance for method chaining
     */
    public function setMeta(string $key, mixed $value): self
    {
        $meta = $this->meta ?? [];
        $meta[$key] = $value;
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get a value from the profile's meta data array.
     *
     * Retrieves a value from the meta JSON column by key.
     * Returns the provided default if the key does not exist.
     *
     * @param string $key The meta key to retrieve
     * @param mixed $default The default value if key is not found
     *
     * @return mixed The meta value or default
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }
}
