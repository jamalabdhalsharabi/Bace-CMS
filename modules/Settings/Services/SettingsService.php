<?php

declare(strict_types=1);

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Contracts\SettingsServiceContract;
use Modules\Settings\Domain\Models\Setting;

class SettingsService implements SettingsServiceContract
{
    protected ?array $settings = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettings();

        return $settings[$key] ?? config("settings.defaults.{$key}", $default);
    }

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

    public function has(string $key): bool
    {
        $settings = $this->loadSettings();
        return isset($settings[$key]);
    }

    public function forget(string $key): bool
    {
        $result = Setting::where('key', $key)->delete() > 0;
        $this->clearCache();

        return $result;
    }

    public function all(): array
    {
        return $this->loadSettings();
    }

    public function group(string $group): array
    {
        return Setting::group($group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    public function public(): array
    {
        return Setting::public()
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

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

    public function clearCache(): void
    {
        $this->settings = null;

        if (config('settings.cache.enabled', true)) {
            Cache::forget(config('settings.cache.key', 'app_settings'));
        }
    }

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

    protected function fetchSettings(): array
    {
        return Setting::all()->pluck('value', 'key')->toArray();
    }

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
