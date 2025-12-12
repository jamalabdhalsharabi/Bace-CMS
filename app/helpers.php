<?php

declare(strict_types=1);

use App\Services\FeatureManager;
use App\Services\ModuleLoader;

if (!function_exists('module_path')) {
    /**
     * Get the path to a module directory.
     */
    function module_path(string $module, string $path = ''): string
    {
        $modulePath = base_path('modules/' . $module);

        return $path ? $modulePath . '/' . ltrim($path, '/') : $modulePath;
    }
}

if (!function_exists('modules')) {
    /**
     * Get the module loader instance.
     */
    function modules(): ModuleLoader
    {
        return app(ModuleLoader::class);
    }
}

if (!function_exists('module')) {
    /**
     * Get a specific module by alias.
     */
    function module(string $alias): ?\App\Contracts\ModuleContract
    {
        return modules()->get($alias);
    }
}

if (!function_exists('module_enabled')) {
    /**
     * Check if a module is enabled.
     */
    function module_enabled(string $alias): bool
    {
        $module = module($alias);
        return $module && $module->isEnabled();
    }
}

if (!function_exists('features')) {
    /**
     * Get the feature manager instance.
     */
    function features(): FeatureManager
    {
        return app(FeatureManager::class);
    }
}

if (!function_exists('feature_enabled')) {
    /**
     * Check if a feature is enabled.
     */
    function feature_enabled(string $feature): bool
    {
        return features()->isEnabled($feature);
    }
}

if (!function_exists('feature_disabled')) {
    /**
     * Check if a feature is disabled.
     */
    function feature_disabled(string $feature): bool
    {
        return features()->isDisabled($feature);
    }
}

if (!function_exists('when_feature')) {
    /**
     * Execute callback when feature is enabled.
     */
    function when_feature(string $feature, callable $callback, ?callable $fallback = null): mixed
    {
        return features()->when($feature, $callback, $fallback);
    }
}
