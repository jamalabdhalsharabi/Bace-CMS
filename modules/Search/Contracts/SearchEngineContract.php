<?php

declare(strict_types=1);

namespace Modules\Search\Contracts;

interface SearchEngineContract
{
    public function index(string $index, string $id, array $data): bool;

    public function delete(string $index, string $id): bool;

    public function search(string $index, array $options = []): array;

    public function createIndex(string $index, array $settings = []): bool;

    public function deleteIndex(string $index): bool;

    public function updateSettings(string $index, array $settings): bool;

    public function flush(string $index): bool;
}
