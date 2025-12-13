<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Webhooks\Domain\Models\Webhook;

class WebhookRepository extends BaseRepository
{
    public function __construct(Webhook $model)
    {
        parent::__construct($model);
    }

    public function getActiveByEvent(string $event): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->get();
    }
}
