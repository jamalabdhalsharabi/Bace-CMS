<?php

declare(strict_types=1);

namespace Modules\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Media\Contracts\MediaServiceContract;
use Modules\Media\Domain\Models\Media;

class GenerateMediaConversions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Media $media
    ) {}

    public function handle(MediaServiceContract $mediaService): void
    {
        $mediaService->generateConversions($this->media);
    }
}
