<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\MediaFolder;

final class DeleteFolderAction extends Action
{
    public function execute(string $folderId): bool
    {
        return MediaFolder::destroy($folderId) > 0;
    }
}
