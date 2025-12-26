<?php

declare(strict_types=1);

namespace Modules\Media\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepository;

/**
 * Update Media Action.
 *
 * Handles updating media metadata including title, alt text, and custom metadata.
 *
 * @package Modules\Media\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateMediaAction extends Action
{
    /**
     * Create a new UpdateMediaAction instance.
     *
     * @param MediaRepository $repository The media repository
     */
    public function __construct(
        private readonly MediaRepository $repository
    ) {}

    /**
     * Execute the media update action.
     *
     * @param Media $media The media instance to update
     * @param array<string, mixed> $data The update data
     * 
     * @return Media The updated media instance
     * 
     * @throws \Exception When update fails
     */
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
