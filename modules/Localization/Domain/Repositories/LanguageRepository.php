<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Localization\Domain\Models\Language;

/**
 * Language Repository.
 *
 * @extends BaseRepository<Language>
 */
final class LanguageRepository extends BaseRepository
{
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getDefault(): ?Language
    {
        return $this->query()->where('is_default', true)->first();
    }

    public function findByCode(string $code): ?Language
    {
        return $this->query()->where('code', $code)->first();
    }
}
