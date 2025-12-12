<?php

declare(strict_types=1);

namespace App\Support;

use App\Contracts\ModuleContract;
use Illuminate\Support\Facades\File;

class Module implements ModuleContract
{
    protected array $manifest;
    protected string $path;
    protected bool $enabled = true;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->loadManifest();
    }

    protected function loadManifest(): void
    {
        $manifestPath = $this->path . '/module.json';

        if (!File::exists($manifestPath)) {
            throw new \RuntimeException("Module manifest not found: {$manifestPath}");
        }

        $this->manifest = json_decode(File::get($manifestPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid module manifest: {$manifestPath}");
        }
    }

    public function getName(): string
    {
        return $this->manifest['name'] ?? basename($this->path);
    }

    public function getAlias(): string
    {
        return $this->manifest['alias'] ?? strtolower($this->getName());
    }

    public function getDescription(): string
    {
        return $this->manifest['description'] ?? '';
    }

    public function getVersion(): string
    {
        return $this->manifest['version'] ?? '1.0.0';
    }

    public function getPriority(): int
    {
        return (int) ($this->manifest['priority'] ?? 100);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && ($this->manifest['enabled'] ?? true);
    }

    public function enable(): void
    {
        $this->enabled = true;
        $this->updateManifest(['enabled' => true]);
    }

    public function disable(): void
    {
        $this->enabled = false;
        $this->updateManifest(['enabled' => false]);
    }

    public function getProviders(): array
    {
        return $this->manifest['providers'] ?? [];
    }

    public function getDependencies(): array
    {
        return $this->manifest['dependencies'] ?? [];
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->manifest, $key, $default);
    }

    public function boot(): void
    {
        // Load module routes
        $this->loadRoutes();

        // Load module views
        $this->loadViews();

        // Load module translations
        $this->loadTranslations();
    }

    public function register(): void
    {
        // Load module config
        $this->loadConfig();

        // Load module helpers
        $this->loadHelpers();
    }

    protected function loadRoutes(): void
    {
        $routesPath = $this->path . '/Routes';

        if (File::isDirectory($routesPath)) {
            $webRoutes = $routesPath . '/web.php';
            $apiRoutes = $routesPath . '/api.php';
            $adminRoutes = $routesPath . '/admin.php';

            if (File::exists($webRoutes)) {
                require $webRoutes;
            }

            if (File::exists($apiRoutes)) {
                require $apiRoutes;
            }

            if (File::exists($adminRoutes)) {
                require $adminRoutes;
            }
        }
    }

    protected function loadViews(): void
    {
        $viewsPath = $this->path . '/Resources/views';

        if (File::isDirectory($viewsPath)) {
            app('view')->addNamespace($this->getAlias(), $viewsPath);
        }
    }

    protected function loadTranslations(): void
    {
        $langPath = $this->path . '/Resources/lang';

        if (File::isDirectory($langPath)) {
            app('translator')->addNamespace($this->getAlias(), $langPath);
        }
    }

    protected function loadConfig(): void
    {
        $configPath = $this->path . '/Config/config.php';

        if (File::exists($configPath)) {
            config()->set($this->getAlias(), require $configPath);
        }
    }

    protected function loadHelpers(): void
    {
        $helpersPath = $this->path . '/Helpers/helpers.php';

        if (File::exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    protected function updateManifest(array $data): void
    {
        $this->manifest = array_merge($this->manifest, $data);
        $manifestPath = $this->path . '/module.json';

        File::put(
            $manifestPath,
            json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'alias' => $this->getAlias(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'priority' => $this->getPriority(),
            'path' => $this->getPath(),
            'enabled' => $this->isEnabled(),
            'providers' => $this->getProviders(),
            'dependencies' => $this->getDependencies(),
        ];
    }
}
