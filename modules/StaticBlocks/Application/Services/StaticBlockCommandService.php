<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Services;

use Modules\StaticBlocks\Application\Actions\CreateStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\DeleteStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\UpdateStaticBlockAction;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

final class StaticBlockCommandService
{
    public function __construct(
        private readonly CreateStaticBlockAction $createAction,
        private readonly UpdateStaticBlockAction $updateAction,
        private readonly DeleteStaticBlockAction $deleteAction,
    ) {}

    public function create(array $data): StaticBlock
    {
        return $this->createAction->execute($data);
    }

    public function update(StaticBlock $block, array $data): StaticBlock
    {
        return $this->updateAction->execute($block, $data);
    }

    public function delete(StaticBlock $block): bool
    {
        return $this->deleteAction->execute($block);
    }
}
