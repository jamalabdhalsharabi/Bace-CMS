<?php

declare(strict_types=1);

namespace Modules\Settings\Application\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Application\Actions\DeleteSettingAction;
use Modules\Settings\Application\Actions\SetSettingAction;
use Modules\Settings\Domain\DTO\SettingData;
use Modules\Settings\Domain\Models\Setting;

/**
 * Setting Command Service.
 */
final class SettingCommandService
{
    public function __construct(
        private readonly SetSettingAction $setAction,
        private readonly DeleteSettingAction $deleteAction,
    ) {}

    public function set(SettingData $data): Setting
    {
        return $this->setAction->execute($data);
    }

    public function setMany(array $settings): void
    {
        $this->setAction->setMany($settings);
    }

    public function forget(string $key): bool
    {
        return $this->deleteAction->execute($key);
    }

    public function clearCache(): void
    {
        if (config('settings.cache.enabled', true)) {
            Cache::forget(config('settings.cache.key', 'app_settings'));
        }
    }
}
