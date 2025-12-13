<?php

declare(strict_types=1);

namespace Modules\Settings\Application\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Application\Actions\Action;
use Modules\Settings\Domain\Models\Setting;

final class DeleteSettingAction extends Action
{
    public function execute(string $key): bool
    {
        $result = Setting::where('key', $key)->delete() > 0;

        if (config('settings.cache.enabled', true)) {
            Cache::forget(config('settings.cache.key', 'app_settings'));
        }

        return $result;
    }
}
