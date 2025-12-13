<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserSetting Model - User-specific key-value settings.
 *
 * This model stores individual user preferences and settings
 * as key-value pairs, allowing flexible per-user configuration.
 *
 * @property string $id UUID primary key
 * @property string $user_id Foreign key to users table
 * @property string $key Setting key identifier (e.g., 'theme', 'notifications_enabled')
 * @property string|null $value Setting value (stored as string, cast as needed)
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read User $user The user who owns this setting
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting query()
 */
class UserSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns this setting.
     *
     * @return BelongsTo<User, UserSetting>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
