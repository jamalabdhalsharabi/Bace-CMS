<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Domain\Repositories\StaticBlockRepository;

final class CreateStaticBlockAction extends Action
{
    public function __construct(
        private readonly StaticBlockRepository $repository
    ) {}

    public function execute(array $data): StaticBlock
    {
        return $this->transaction(function () use ($data) {
            $block = $this->repository->create([
                'identifier' => $data['identifier'] ?? Str::slug($data['translations'][array_key_first($data['translations'])]['title']),
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $this->userId(),
            ]);

            foreach ($data['translations'] as $locale => $trans) {
                $block->translations()->create([
                    'locale' => $locale,
                    'title' => $trans['title'],
                    'content' => $trans['content'] ?? null,
                ]);
            }

            return $block->fresh(['translations']);
        });
    }
}
