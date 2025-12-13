<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Forms\Domain\Models\FormSubmission;

final class FormSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly FormSubmission $submission
    ) {}
}
