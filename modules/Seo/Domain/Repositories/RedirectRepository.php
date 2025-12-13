<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Seo\Domain\Models\Redirect;

class RedirectRepository extends BaseRepository
{
    public function __construct(Redirect $model)
    {
        parent::__construct($model);
    }

    public function findBySourceUrl(string $url): ?Redirect
    {
        return $this->model->where('source_url', $url)->where('is_active', true)->first();
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->orderBy('priority')->get();
    }
}
