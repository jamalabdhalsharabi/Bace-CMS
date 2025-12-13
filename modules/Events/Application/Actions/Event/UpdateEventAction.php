<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\DTO\EventData;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

final class UpdateEventAction extends Action
{
    public function __construct(
        private readonly EventRepository $repository
    ) {}

    public function execute(Event $event, EventData $data): Event
    {
        return $this->transaction(function () use ($event, $data) {
            $this->repository->update($event->id, [
                'is_featured' => $data->is_featured,
                'event_type' => $data->event_type ?? $event->event_type,
                'venue_name' => $data->venue_name ?? $event->venue_name,
                'venue_address' => $data->venue_address ?? $event->venue_address,
                'latitude' => $data->latitude ?? $event->latitude,
                'longitude' => $data->longitude ?? $event->longitude,
                'is_online' => $data->is_online,
                'online_url' => $data->online_url ?? $event->online_url,
                'start_date' => $data->start_date ?? $event->start_date,
                'end_date' => $data->end_date ?? $event->end_date,
                'timezone' => $data->timezone ?? $event->timezone,
                'max_attendees' => $data->max_attendees ?? $event->max_attendees,
                'registration_deadline' => $data->registration_deadline ?? $event->registration_deadline,
                'is_free' => $data->is_free,
                'featured_image_id' => $data->featured_image_id ?? $event->featured_image_id,
                'meta' => $data->meta ?? $event->meta,
                'updated_by' => $this->userId(),
            ]);

            foreach ($data->translations as $locale => $trans) {
                $event->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'description' => $trans['description'] ?? null,
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]
                );
            }

            if ($data->category_ids !== null) {
                $event->categories()->sync($data->category_ids);
            }

            return $event->fresh(['translations', 'categories']);
        });
    }
}
