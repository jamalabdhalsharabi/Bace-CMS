<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FeatureManager
{
    protected array $features = [];
    protected array $resolvedFeatures = [];
    protected string $cacheKey = 'cms_features';
    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $this->loadFeatures();
    }

    /**
     * Load features from config.
     */
    protected function loadFeatures(): void
    {
        $this->features = config('features', []);
    }

    /**
     * Check if a feature is enabled.
     */
    public function isEnabled(string $feature): bool
    {
        // Check cache first
        if (isset($this->resolvedFeatures[$feature])) {
            return $this->resolvedFeatures[$feature];
        }

        $enabled = $this->resolveFeature($feature);
        $this->resolvedFeatures[$feature] = $enabled;

        return $enabled;
    }

    /**
     * Check if a feature is disabled.
     */
    public function isDisabled(string $feature): bool
    {
        return !$this->isEnabled($feature);
    }

    /**
     * Resolve a feature's enabled status.
     */
    protected function resolveFeature(string $feature): bool
    {
        // Check if feature exists in config
        if (!array_key_exists($feature, $this->features)) {
            return false;
        }

        $config = $this->features[$feature];

        // Simple boolean
        if (is_bool($config)) {
            return $config;
        }

        // Array config with 'enabled' key
        if (is_array($config)) {
            // Check 'enabled' flag
            if (isset($config['enabled']) && !$config['enabled']) {
                return false;
            }

            // Check environment
            if (isset($config['environments'])) {
                $currentEnv = app()->environment();
                if (!in_array($currentEnv, $config['environments'], true)) {
                    return false;
                }
            }

            // Check dependencies
            if (isset($config['depends_on'])) {
                foreach ((array) $config['depends_on'] as $dependency) {
                    if (!$this->isEnabled($dependency)) {
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Enable a feature at runtime.
     */
    public function enable(string $feature): self
    {
        $this->features[$feature] = true;
        $this->resolvedFeatures[$feature] = true;
        $this->clearCache();

        return $this;
    }

    /**
     * Disable a feature at runtime.
     */
    public function disable(string $feature): self
    {
        $this->features[$feature] = false;
        $this->resolvedFeatures[$feature] = false;
        $this->clearCache();

        return $this;
    }

    /**
     * Execute callback if feature is enabled.
     */
    public function when(string $feature, callable $callback, ?callable $fallback = null): mixed
    {
        if ($this->isEnabled($feature)) {
            return $callback();
        }

        if ($fallback) {
            return $fallback();
        }

        return null;
    }

    /**
     * Execute callback if feature is disabled.
     */
    public function unless(string $feature, callable $callback): mixed
    {
        if ($this->isDisabled($feature)) {
            return $callback();
        }

        return null;
    }

    /**
     * Get all features.
     */
    public function all(): array
    {
        return $this->features;
    }

    /**
     * Get all enabled features.
     */
    public function getEnabled(): array
    {
        return array_keys(
            array_filter(
                $this->features,
                fn ($feature, $key) => $this->isEnabled($key),
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    /**
     * Get all disabled features.
     */
    public function getDisabled(): array
    {
        return array_keys(
            array_filter(
                $this->features,
                fn ($feature, $key) => $this->isDisabled($key),
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    /**
     * Set features from array.
     */
    public function setFeatures(array $features): self
    {
        $this->features = array_merge($this->features, $features);
        $this->resolvedFeatures = [];

        return $this;
    }

    /**
     * Get feature config.
     */
    public function getConfig(string $feature): mixed
    {
        return $this->features[$feature] ?? null;
    }

    /**
     * Clear the feature cache.
     */
    public function clearCache(): void
    {
        $this->resolvedFeatures = [];
        Cache::forget($this->cacheKey);
    }

    /**
     * Define a new feature.
     */
    public function define(string $feature, bool|array $config = true): self
    {
        $this->features[$feature] = $config;
        unset($this->resolvedFeatures[$feature]);

        return $this;
    }

    /**
     * Check multiple features at once.
     */
    public function allEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if (!$this->isEnabled($feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if any of the features is enabled.
     */
    public function anyEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if ($this->isEnabled($feature)) {
                return true;
            }
        }

        return false;
    }
}
