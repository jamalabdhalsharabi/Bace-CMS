<?php

declare(strict_types=1);

namespace Modules\Settings\Contracts;

interface SettingsServiceContract
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?string $group = null, ?string $type = null): void;

    public function has(string $key): bool;

    public function forget(string $key): bool;

    public function all(): array;

    public function group(string $group): array;

    public function public(): array;

    public function setMany(array $settings): void;

    public function clearCache(): void;
}
