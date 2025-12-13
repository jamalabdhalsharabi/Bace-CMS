<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MediaDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $mediaId,
        public readonly string $path,
    ) {}
}
