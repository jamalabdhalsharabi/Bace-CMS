<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;

/**
 * Language Query Service.
 */
final class LanguageQueryService
{
    public function __construct(
        private readonly LanguageRepository $repository
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?Language
    {
        return $this->repository->find($id);
    }

    public function findByCode(string $code): ?Language
    {
        return $this->repository->findByCode($code);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getDefault(): ?Language
    {
        return $this->repository->getDefault();
    }

    public function getActiveCodes(): array
    {
        return $this->repository
            ->getActive()
            ->pluck('code')
            ->toArray();
    }
}
