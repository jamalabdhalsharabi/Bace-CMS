<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Settings\Domain\Models\Setting;

/**
 * Setting Repository.
 *
 * @extends BaseRepository<Setting>
 */
final class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function findByKey(string $key): ?Setting
    {
        return $this->query()->where('key', $key)->first();
    }

    public function getByGroup(string $group): Collection
    {
        return $this->query()->where('group', $group)->get();
    }

    public function getPublic(): Collection
    {
        return $this->query()->where('is_public', true)->get();
    }

    public function getAllAsKeyValue(): array
    {
        return $this->query()->pluck('value', 'key')->toArray();
    }
}
