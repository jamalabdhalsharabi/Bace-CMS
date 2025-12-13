<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

final class UpdateStaticBlockAction extends Action
{
    public function __construct(
        private readonly StaticBlockRepository $repository
    ) {}

    public function execute(StaticBlock $block, array $data): StaticBlock
    {
        return $this->transaction(function () use ($block, $data) {
            $this->repository->update($block->id, [
                'identifier' => $data['identifier'] ?? $block->identifier,
                'is_active' => $data['is_active'] ?? $block->is_active,
                'updated_by' => $this->userId(),
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $block->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['title' => $trans['title'], 'content' => $trans['content'] ?? null]
                    );
                }
            }

            return $block->fresh(['translations']);
        });
    }
}
