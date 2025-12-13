<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

class StaticBlockRepository extends BaseRepository
{
    public function __construct(StaticBlock $model)
    {
        parent::__construct($model);
    }

    public function findByIdentifier(string $identifier): ?StaticBlock
    {
        return $this->model->where('identifier', $identifier)->where('is_active', true)->first();
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->with('translations')->get();
    }
}
