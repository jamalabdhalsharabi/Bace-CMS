<?php

declare(strict_types=1);

namespace Modules\Services\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Services\Domain\Models\Service;

final class DuplicateServiceAction extends Action
{
    public function execute(Service $service): Service
    {
        return $this->transaction(function () use ($service) {
            $clone = $service->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($service->translations as $trans) {
                $clone->translations()->create([
                    'locale' => $trans->locale,
                    'title' => $trans->title . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'description' => $trans->description,
                    'content' => $trans->content,
                ]);
            }

            return $clone->fresh(['translations']);
        });
    }
}
