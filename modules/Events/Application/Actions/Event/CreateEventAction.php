<?php

declare(strict_types=1);

namespace Modules\Events\Application\Actions\Event;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Events\Domain\DTO\EventData;
use Modules\Events\Domain\Events\EventCreated;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

/**
 * Create Event Action.
 */
final class CreateEventAction extends Action
{
    public function __construct(
        private readonly EventRepository $repository
    ) {}

    public function execute(EventData $data): Event
    {
        return $this->transaction(function () use ($data) {
            $event = $this->repository->create([
                'status' => $data->status,
                'is_featured' => $data->is_featured,
                'event_type' => $data->event_type,
                'venue_name' => $data->venue_name,
                'venue_address' => $data->venue_address,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'is_online' => $data->is_online,
                'online_url' => $data->online_url,
                'start_date' => $data->start_date ? Carbon::parse($data->start_date) : null,
                'end_date' => $data->end_date ? Carbon::parse($data->end_date) : null,
                'timezone' => $data->timezone,
                'max_attendees' => $data->max_attendees,
                'registration_deadline' => $data->registration_deadline ? Carbon::parse($data->registration_deadline) : null,
                'is_free' => $data->is_free,
                'featured_image_id' => $data->featured_image_id,
                'meta' => $data->meta,
                'created_by' => $this->userId(),
            ]);

            $this->createTranslations($event, $data->translations);

            event(new EventCreated($event));

            return $event->fresh(['translations', 'ticketTypes']);
        });
    }

    private function createTranslations(Event $event, array $translations): void
    {
        foreach ($translations as $locale => $trans) {
            $event->translations()->create([
                'locale' => $locale,
                'title' => $trans['title'],
                'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                'description' => $trans['description'] ?? null,
                'short_description' => $trans['short_description'] ?? null,
                'meta_title' => $trans['meta_title'] ?? null,
                'meta_description' => $trans['meta_description'] ?? null,
            ]);
        }
    }
}
