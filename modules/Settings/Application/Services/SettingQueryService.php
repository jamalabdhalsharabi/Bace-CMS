<?php

declare(strict_types=1);

namespace Modules\Settings\Application\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Domain\Repositories\SettingRepository;

/**
 * Setting Query Service.
 */
final class SettingQueryService
{
    private ?array $settings = null;

    public function __construct(
        private readonly SettingRepository $repository
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettings();

        return $settings[$key] ?? config("settings.defaults.{$key}", $default);
    }

    public function has(string $key): bool
    {
        $settings = $this->loadSettings();

        return isset($settings[$key]);
    }

    public function all(): array
    {
        return $this->loadSettings();
    }

    public function group(string $group): array
    {
        return $this->repository
            ->getByGroup($group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function public(): array
    {
        return $this->repository
            ->getPublic()
            ->pluck('value', 'key')
            ->toArray();
    }

    private function loadSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        if (config('settings.cache.enabled', true)) {
            $this->settings = Cache::remember(
                config('settings.cache.key', 'app_settings'),
                config('settings.cache.ttl', 3600),
                fn () => $this->repository->getAllAsKeyValue()
            );
        } else {
            $this->settings = $this->repository->getAllAsKeyValue();
        }

        return $this->settings;
    }
}
