<?php

declare(strict_types=1);

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Contracts\SettingsServiceContract;
use Modules\Settings\Domain\Models\Setting;

/**
 * Class SettingsService
 *
 * Service class for managing application settings
 * including caching, groups, and public settings.
 *
 * @package Modules\Settings\Services
 */
class SettingsService implements SettingsServiceContract
{
    /**
     * Cached settings array.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $settings = null;

    /**
     * Get a setting value by key.
     *
     * @param string $key The setting key
     * @param mixed $default Default value if not found
     *
     * @return mixed The setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettings();

        return $settings[$key] ?? config("settings.defaults.{$key}", $default);
    }

    /**
     * Set a setting value.
     *
     * @param string $key The setting key
     * @param mixed $value The value to store
     * @param string|null $group Optional setting group
     * @param string|null $type Optional value type
     *
     * @return void
     */
    public function set(string $key, mixed $value, ?string $group = null, ?string $type = null): void
    {
        $existing = Setting::where('key', $key)->first();

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group ?? $existing?->group ?? 'general',
                'type' => $type ?? $existing?->type ?? $this->detectType($value),
            ]
        );

        $this->clearCache();
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key The setting key
     *
     * @return bool True if setting exists
     */
    public function has(string $key): bool
    {
        $settings = $this->loadSettings();
        return isset($settings[$key]);
    }

    /**
     * Remove a setting.
     *
     * @param string $key The setting key to remove
     *
     * @return bool True if deleted
     */
    public function forget(string $key): bool
    {
        $result = Setting::where('key', $key)->delete() > 0;
        $this->clearCache();

        return $result;
    }

    /**
     * Get all settings.
     *
     * @return array All settings as key-value pairs
     */
    public function all(): array
    {
        return $this->loadSettings();
    }

    /**
     * Get settings by group.
     *
     * @param string $group The group name
     *
     * @return array Settings in the group
     */
    public function group(string $group): array
    {
        return Setting::group($group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get public settings.
     *
     * @return array Public settings as key-value pairs
     */
    public function public(): array
    {
        return Setting::public()
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Set multiple settings at once.
     *
     * @param array $settings Array of key-value pairs
     *
     * @return void
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (is_array($value) && isset($value['value'])) {
                $this->set($key, $value['value'], $value['group'] ?? null, $value['type'] ?? null);
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->settings = null;

        if (config('settings.cache.enabled', true)) {
            Cache::forget(config('settings.cache.key', 'app_settings'));
        }
    }

    /**
     * Load settings from cache or database.
     *
     * @return array All settings
     */
    protected function loadSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        if (config('settings.cache.enabled', true)) {
            $this->settings = Cache::remember(
                config('settings.cache.key', 'app_settings'),
                config('settings.cache.ttl', 3600),
                fn () => $this->fetchSettings()
            );
        } else {
            $this->settings = $this->fetchSettings();
        }

        return $this->settings;
    }

    /**
     * Fetch all settings from database.
     *
     * @return array All settings
     */
    protected function fetchSettings(): array
    {
        return Setting::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Detect the type of a value.
     *
     * @param mixed $value The value to check
     *
     * @return string The detected type
     */
    protected function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}
