<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model - Stores application configuration settings.
 *
 * This model provides key-value storage for application settings
 * with type casting, grouping, and public/private visibility.
 *
 * @property string $id UUID primary key
 * @property string $group Setting group (e.g., 'general', 'email', 'seo')
 * @property string $key Unique setting key identifier
 * @property mixed $value Setting value (auto-cast based on type)
 * @property string $type Value type (string, boolean, integer, float, array, json)
 * @property bool $is_public Whether setting is accessible publicly
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting group(string $group) Filter by group
 * @method static \Illuminate\Database\Eloquent\Builder|Setting public() Filter public settings
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 */
class Setting extends Model
{
    use HasUuids;

    protected $table = 'settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the setting value with automatic type casting.
     *
     * @param string|null $value The raw stored value
     * @return mixed The cast value based on type
     */
    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Set the setting value with automatic serialization.
     *
     * @param mixed $value The value to store
     * @return void
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = match ($this->type ?? 'string') {
            'array', 'json' => is_array($value) ? json_encode($value) : $value,
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * Scope to filter settings by group.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Setting> $query
     * @param string $group The group name
     * @return \Illuminate\Database\Eloquent\Builder<Setting>
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter only public settings.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Setting> $query
     * @return \Illuminate\Database\Eloquent\Builder<Setting>
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key The setting key
     * @param mixed $default Default value if not found
     * @return mixed The setting value or default
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Set a setting value, creating or updating as needed.
     *
     * @param string $key The setting key
     * @param mixed $value The value to store
     * @param string $group The setting group
     * @param string $type The value type
     * @return static The created or updated setting
     */
    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'string'): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
    }
}
