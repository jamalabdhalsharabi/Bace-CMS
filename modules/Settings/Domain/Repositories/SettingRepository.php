<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Settings\Domain\Models\Setting;

/**
 * Setting Repository.
 *
 * Read-only repository for Setting model queries.
 * All write operations must be performed through Action classes.
 *
 * @extends BaseRepository<Setting>
 *
 * @package Modules\Settings\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SettingRepository extends BaseRepository
{
    /**
     * Create a new SettingRepository instance.
     *
     * @param Setting $model The Setting model instance
     */
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
