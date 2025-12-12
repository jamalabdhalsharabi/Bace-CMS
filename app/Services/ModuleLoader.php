<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ModuleContract;
use App\Support\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ModuleLoader
{
    protected Collection $modules;
    protected string $modulesPath;
    protected array $bootedModules = [];

    public function __construct()
    {
        $this->modules = collect();
        $this->modulesPath = config('modules.path', base_path('modules'));
    }

    /**
     * Scan and load all modules.
     */
    public function scan(): self
    {
        if (!File::isDirectory($this->modulesPath)) {
            File::makeDirectory($this->modulesPath, 0755, true);
            return $this;
        }

        $directories = File::directories($this->modulesPath);

        foreach ($directories as $directory) {
            $manifestPath = $directory . '/module.json';

            if (File::exists($manifestPath)) {
                try {
                    $module = new Module($directory);
                    $this->modules->put($module->getAlias(), $module);
                } catch (\Exception $e) {
                    report($e);
                }
            }
        }

        // Sort by priority
        $this->modules = $this->modules->sortBy(fn (Module $m) => $m->getPriority());

        return $this;
    }

    /**
     * Register all enabled modules.
     */
    public function register(): void
    {
        foreach ($this->getEnabled() as $module) {
            $this->registerModule($module);
        }
    }

    /**
     * Boot all enabled modules.
     */
    public function boot(): void
    {
        foreach ($this->getEnabled() as $module) {
            $this->bootModule($module);
        }
    }

    /**
     * Register a single module.
     */
    protected function registerModule(ModuleContract $module): void
    {
        // Check dependencies
        foreach ($module->getDependencies() as $dependency) {
            if (!$this->has($dependency)) {
                throw new \RuntimeException(
                    "Module [{$module->getName()}] requires [{$dependency}] which is not installed."
                );
            }

            $depModule = $this->get($dependency);
            if (!$depModule->isEnabled()) {
                throw new \RuntimeException(
                    "Module [{$module->getName()}] requires [{$dependency}] which is not enabled."
                );
            }
        }

        // Register module
        $module->register();

        // Register service providers
        foreach ($module->getProviders() as $provider) {
            if (class_exists($provider)) {
                app()->register($provider);
            }
        }
    }

    /**
     * Boot a single module.
     */
    protected function bootModule(ModuleContract $module): void
    {
        if (in_array($module->getAlias(), $this->bootedModules, true)) {
            return;
        }

        $module->boot();
        $this->bootedModules[] = $module->getAlias();
    }

    /**
     * Get all modules.
     */
    public function all(): Collection
    {
        return $this->modules;
    }

    /**
     * Get all enabled modules.
     */
    public function getEnabled(): Collection
    {
        return $this->modules->filter(fn (Module $m) => $m->isEnabled());
    }

    /**
     * Get all disabled modules.
     */
    public function getDisabled(): Collection
    {
        return $this->modules->filter(fn (Module $m) => !$m->isEnabled());
    }

    /**
     * Get a module by alias.
     */
    public function get(string $alias): ?ModuleContract
    {
        return $this->modules->get($alias);
    }

    /**
     * Check if a module exists.
     */
    public function has(string $alias): bool
    {
        return $this->modules->has($alias);
    }

    /**
     * Enable a module.
     */
    public function enable(string $alias): bool
    {
        $module = $this->get($alias);

        if (!$module) {
            return false;
        }

        $module->enable();
        return true;
    }

    /**
     * Disable a module.
     */
    public function disable(string $alias): bool
    {
        $module = $this->get($alias);

        if (!$module) {
            return false;
        }

        $module->disable();
        return true;
    }

    /**
     * Get modules path.
     */
    public function getPath(): string
    {
        return $this->modulesPath;
    }

    /**
     * Get module path by alias.
     */
    public function getModulePath(string $alias): ?string
    {
        $module = $this->get($alias);
        return $module?->getPath();
    }

    /**
     * Get count of modules.
     */
    public function count(): int
    {
        return $this->modules->count();
    }

    /**
     * Get modules as array.
     */
    public function toArray(): array
    {
        return $this->modules->map(fn (Module $m) => $m->toArray())->toArray();
    }
}
