<?php

declare(strict_types=1);

namespace Modules\Search\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Search\Contracts\SearchServiceContract;

class IndexContent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public object $model,
        public string $action = 'index'
    ) {}

    public function handle(SearchServiceContract $searchService): void
    {
        if ($this->action === 'delete') {
            $searchService->removeModel($this->model);
        } else {
            $searchService->indexModel($this->model);
        }
    }
}
