<?php

declare(strict_types=1);

namespace Modules\Events\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'event_type' => $this->event_type,
            'is_featured' => $this->is_featured,
            'is_online' => $this->is_online,
            'is_free' => $this->is_free,
            'venue' => $this->when(!$this->is_online, ['name' => $this->venue_name, 'address' => $this->venue_address]),
            'online_url' => $this->when($this->is_online, $this->online_url),
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'max_attendees' => $this->max_attendees,
            'available_spots' => $this->getAvailableSpots(),
            'ticket_types' => $this->when($this->ticketTypes->isNotEmpty(), fn() => $this->ticketTypes->map(fn($t) => [
                'id' => $t->id, 'name' => $t->name, 'price' => $t->price, 'available' => $t->getAvailableQuantity(),
            ])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
