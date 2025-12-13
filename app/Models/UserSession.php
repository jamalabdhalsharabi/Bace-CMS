<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserSession Model - Tracks active user sessions.
 *
 * This model represents user sessions stored in the database,
 * enabling session management, device tracking, and security features.
 *
 * @property string $id Session ID (string, not auto-incrementing)
 * @property string|null $user_id Foreign key to users table (null for guests)
 * @property string $payload Serialized session data
 * @property string|null $ip_address Client IP address (IPv4 or IPv6)
 * @property string|null $user_agent Browser/client user agent string
 * @property int $last_activity Unix timestamp of last activity
 * @property string|null $device_name Friendly device name (e.g., 'Chrome on Windows')
 * @property bool $is_current Whether this is the current active session
 *
 * @property-read User|null $user The authenticated user for this session
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession query()
 */
class UserSession extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_sessions';

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'payload',
        'ip_address',
        'user_agent',
        'last_activity',
        'device_name',
        'is_current',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_activity' => 'integer',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this session.
     *
     * @return BelongsTo<User, UserSession>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
