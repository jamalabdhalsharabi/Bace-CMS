<?php

declare(strict_types=1);

namespace App\Contracts;

interface ModuleContract
{
    /**
     * Get the module name.
     */
    public function getName(): string;

    /**
     * Get the module alias (lowercase identifier).
     */
    public function getAlias(): string;

    /**
     * Get the module description.
     */
    public function getDescription(): string;

    /**
     * Get the module version.
     */
    public function getVersion(): string;

    /**
     * Get the module priority (lower = loaded first).
     */
    public function getPriority(): int;

    /**
     * Get the module path.
     */
    public function getPath(): string;

    /**
     * Check if the module is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Enable the module.
     */
    public function enable(): void;

    /**
     * Disable the module.
     */
    public function disable(): void;

    /**
     * Get the module's service providers.
     *
     * @return array<class-string>
     */
    public function getProviders(): array;

    /**
     * Get the module's dependencies.
     *
     * @return array<string>
     */
    public function getDependencies(): array;

    /**
     * Boot the module.
     */
    public function boot(): void;

    /**
     * Register the module.
     */
    public function register(): void;
}
