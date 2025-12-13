<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\MediaFolder;

final class CreateFolderAction extends Action
{
    public function execute(array $data): MediaFolder
    {
        return MediaFolder::create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'user_id' => $this->userId(),
        ]);
    }
}
