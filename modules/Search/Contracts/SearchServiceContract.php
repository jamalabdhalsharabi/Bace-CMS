<?php

declare(strict_types=1);

namespace Modules\Search\Contracts;

interface SearchServiceContract
{
    public function search(string $query, array $options = []): array;

    public function searchIndex(string $index, string $query, array $options = []): array;

    public function indexModel(object $model): bool;

    public function removeModel(object $model): bool;

    public function reindex(string $index): int;

    public function reindexAll(): array;
}
