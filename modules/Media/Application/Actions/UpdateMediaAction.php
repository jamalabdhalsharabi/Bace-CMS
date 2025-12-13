<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

final class UpdateMediaAction extends Action
{
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    public function execute(Media $media, array $data): Media
    {
        $this->repository->update($media->id, [
            'alt_text' => $data['alt_text'] ?? $media->alt_text,
            'title' => $data['title'] ?? $media->title,
            'meta' => array_merge($media->meta ?? [], $data['meta'] ?? []),
        ]);

        return $media->fresh();
    }
}
