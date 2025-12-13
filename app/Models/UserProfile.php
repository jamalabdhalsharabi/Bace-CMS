<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserProfile Model - Extended profile information for users.
 *
 * This model stores additional personal and professional information
 * that is not part of the core user authentication data.
 *
 * @property string $id UUID primary key
 * @property string $user_id Foreign key to users table
 * @property string|null $first_name User's first name
 * @property string|null $last_name User's last name
 * @property string|null $phone Phone number with country code
 * @property \Carbon\Carbon|null $date_of_birth User's birth date
 * @property string|null $gender Gender (male, female, other, prefer_not_to_say)
 * @property string|null $address Street address
 * @property string|null $city City name
 * @property string|null $state State or province
 * @property string|null $country ISO country code (e.g., 'SA', 'US')
 * @property string|null $postal_code Postal or ZIP code
 * @property string|null $bio Short biography or description
 * @property string|null $website Personal website URL
 * @property array|null $social_links Social media links as JSON {platform: url}
 * @property string|null $company Company or organization name
 * @property string|null $job_title Professional job title
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read User $user The associated user account
 * @property-read string $full_name Computed full name from first and last name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile query()
 */
class UserProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'bio',
        'website',
        'social_links',
        'company',
        'job_title',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'social_links' => 'array',
        ];
    }

    /**
     * Get the user that owns this profile.
     *
     * @return BelongsTo<User, UserProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's full name.
     *
     * Combines first and last name, falling back to the user's
     * display name if neither is set.
     *
     * @return string The full name or user display name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->user->name;
    }
}
