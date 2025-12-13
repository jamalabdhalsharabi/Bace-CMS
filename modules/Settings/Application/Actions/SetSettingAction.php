<?php

declare(strict_types=1);

namespace Modules\Settings\Application\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Application\Actions\Action;
use Modules\Settings\Domain\DTO\SettingData;
use Modules\Settings\Domain\Models\Setting;

final class SetSettingAction extends Action
{
    public function execute(SettingData $data): Setting
    {
        $existing = Setting::where('key', $data->key)->first();

        $setting = Setting::updateOrCreate(
            ['key' => $data->key],
            [
                'value' => $data->value,
                'group' => $data->group ?? $existing?->group ?? 'general',
                'type' => $data->type ?? $existing?->type ?? $this->detectType($data->value),
                'is_public' => $data->is_public,
            ]
        );

        $this->clearCache();

        return $setting;
    }

    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (is_array($value) && isset($value['value'])) {
                $this->execute(new SettingData(
                    key: $key,
                    value: $value['value'],
                    group: $value['group'] ?? 'general',
                    type: $value['type'] ?? null,
                    is_public: $value['is_public'] ?? false,
                ));
            } else {
                $this->execute(new SettingData(key: $key, value: $value));
            }
        }
    }

    private function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    private function clearCache(): void
    {
        if (config('settings.cache.enabled', true)) {
            Cache::forget(config('settings.cache.key', 'app_settings'));
        }
    }
}
