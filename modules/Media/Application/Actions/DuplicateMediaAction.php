<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\Media;

final class DuplicateMediaAction extends Action
{
    public function execute(Media $media): Media
    {
        $clone = $media->replicate();
        $clone->filename = 'copy-' . $media->filename;
        $clone->save();

        return $clone;
    }
}
