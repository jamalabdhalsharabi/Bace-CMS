<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

final class DeleteStaticBlockAction extends Action
{
    public function __construct(
        private readonly StaticBlockRepository $repository
    ) {}

    public function execute(StaticBlock $block): bool
    {
        $block->translations()->delete();
        return $this->repository->delete($block->id);
    }
}
