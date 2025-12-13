<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\Models\Event;

final class DuplicateEventAction extends Action
{
    public function execute(Event $event): Event
    {
        return $this->transaction(function () use ($event) {
            $clone = $event->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($event->translations as $trans) {
                $clone->translations()->create([
                    'locale' => $trans->locale,
                    'title' => $trans->title . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'description' => $trans->description,
                    'content' => $trans->content,
                ]);
            }

            $clone->categories()->sync($event->categories->pluck('id'));

            return $clone->fresh(['translations', 'categories']);
        });
    }
}
