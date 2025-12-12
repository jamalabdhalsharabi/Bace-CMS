<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ProfileLoader
{
    protected array $profiles = [];
    protected ?string $activeProfile = null;
    protected string $profilesPath;

    public function __construct()
    {
        $this->profilesPath = config('profiles.path', base_path('config/profiles'));
    }

    /**
     * Load a profile by name.
     */
    public function load(string $name): array
    {
        if (isset($this->profiles[$name])) {
            return $this->profiles[$name];
        }

        $profilePath = $this->getProfilePath($name);

        if (!File::exists($profilePath)) {
            throw new \RuntimeException("Profile not found: {$name}");
        }

        $content = File::get($profilePath);
        $profile = Yaml::parse($content);

        $this->profiles[$name] = $profile;

        return $profile;
    }

    /**
     * Apply a profile to the application.
     */
    public function apply(string $name): void
    {
        $profile = $this->load($name);

        // Apply features
        if (isset($profile['features'])) {
            $this->applyFeatures($profile['features']);
        }

        // Apply modules
        if (isset($profile['modules'])) {
            $this->applyModules($profile['modules']);
        }

        // Apply settings
        if (isset($profile['settings'])) {
            $this->applySettings($profile['settings']);
        }

        $this->activeProfile = $name;
    }

    /**
     * Apply feature toggles from profile.
     */
    protected function applyFeatures(array $features): void
    {
        $featureManager = app(FeatureManager::class);

        foreach ($features as $feature => $enabled) {
            if ($enabled) {
                $featureManager->enable($feature);
            } else {
                $featureManager->disable($feature);
            }
        }
    }

    /**
     * Apply module toggles from profile.
     */
    protected function applyModules(array $modules): void
    {
        $moduleLoader = app(ModuleLoader::class);

        foreach ($modules as $module => $enabled) {
            if ($enabled) {
                $moduleLoader->enable($module);
            } else {
                $moduleLoader->disable($module);
            }
        }
    }

    /**
     * Apply settings from profile.
     */
    protected function applySettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            config([$key => $value]);
        }
    }

    /**
     * Get available profiles.
     */
    public function getAvailable(): array
    {
        if (!File::isDirectory($this->profilesPath)) {
            return [];
        }

        $files = File::glob($this->profilesPath . '/*.yaml');
        $profiles = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $profiles[$name] = $this->getProfileInfo($name);
        }

        return $profiles;
    }

    /**
     * Get profile info without loading full config.
     */
    public function getProfileInfo(string $name): array
    {
        $profile = $this->load($name);

        return [
            'name' => $profile['name'] ?? $name,
            'description' => $profile['description'] ?? '',
            'version' => $profile['version'] ?? '1.0.0',
        ];
    }

    /**
     * Get active profile name.
     */
    public function getActive(): ?string
    {
        return $this->activeProfile;
    }

    /**
     * Get profile file path.
     */
    protected function getProfilePath(string $name): string
    {
        return $this->profilesPath . '/' . $name . '.yaml';
    }

    /**
     * Create a new profile.
     */
    public function create(string $name, array $config): void
    {
        $path = $this->getProfilePath($name);

        if (File::exists($path)) {
            throw new \RuntimeException("Profile already exists: {$name}");
        }

        if (!File::isDirectory($this->profilesPath)) {
            File::makeDirectory($this->profilesPath, 0755, true);
        }

        $yaml = Yaml::dump($config, 4, 2);
        File::put($path, $yaml);
    }

    /**
     * Delete a profile.
     */
    public function delete(string $name): bool
    {
        $path = $this->getProfilePath($name);

        if (!File::exists($path)) {
            return false;
        }

        return File::delete($path);
    }

    /**
     * Export current configuration as profile.
     */
    public function exportCurrent(string $name): void
    {
        $featureManager = app(FeatureManager::class);
        $moduleLoader = app(ModuleLoader::class);

        $config = [
            'name' => $name,
            'description' => 'Exported profile',
            'version' => '1.0.0',
            'features' => [],
            'modules' => [],
        ];

        // Export features
        foreach ($featureManager->all() as $feature => $enabled) {
            $config['features'][$feature] = $featureManager->isEnabled($feature);
        }

        // Export modules
        foreach ($moduleLoader->all() as $module) {
            $config['modules'][$module->getAlias()] = $module->isEnabled();
        }

        $this->create($name, $config);
    }
}
