<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Comments\Domain\Models\Comment;

final class CommentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Comment $comment
    ) {}
}
