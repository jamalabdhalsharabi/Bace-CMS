<?php

declare(strict_types=1);

namespace Modules\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Settings\Contracts\SettingsServiceContract;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value, ?string $group = null, ?string $type = null)
 * @method static bool has(string $key)
 * @method static bool forget(string $key)
 * @method static array all()
 * @method static array group(string $group)
 * @method static array public()
 * @method static void setMany(array $settings)
 * @method static void clearCache()
 *
 * @see \Modules\Settings\Services\SettingsService
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsServiceContract::class;
    }
}
