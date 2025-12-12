<?php

declare(strict_types=1);

namespace Modules\Events\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Events\Contracts\EventServiceContract;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

class EventService implements EventServiceContract
{
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Event::with(['translation', 'ticketTypes']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['upcoming'])) $query->upcoming();
        if (isset($filters['featured'])) $query->featured();
        return $query->orderBy('start_date')->paginate($perPage);
    }

    public function find(string $id): ?Event
    {
        return Event::with(['translations', 'ticketTypes', 'registrations'])->find($id);
    }

    public function findBySlug(string $slug): ?Event
    {
        return Event::whereHas('translations', fn($q) => $q->where('slug', $slug))
            ->with(['translations', 'ticketTypes'])->first();
    }

    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $event = Event::create([
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? false,
                'event_type' => $data['event_type'] ?? null,
                'venue_name' => $data['venue_name'] ?? null,
                'venue_address' => $data['venue_address'] ?? null,
                'is_online' => $data['is_online'] ?? false,
                'online_url' => $data['online_url'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'timezone' => $data['timezone'] ?? config('app.timezone'),
                'max_attendees' => $data['max_attendees'] ?? null,
                'registration_deadline' => $data['registration_deadline'] ?? null,
                'is_free' => $data['is_free'] ?? true,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $event->translations()->create([
                        'locale' => $locale,
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'excerpt' => $trans['excerpt'] ?? null,
                        'description' => $trans['description'] ?? null,
                    ]);
                }
            }

            return $event->fresh(['translations']);
        });
    }

    public function update(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $event->update(array_filter($data, fn($v) => $v !== null));
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $event->translations()->updateOrCreate(['locale' => $locale], $trans);
                }
            }
            return $event->fresh(['translations']);
        });
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    public function register(Event $event, array $data): EventRegistration
    {
        $ticketType = $event->ticketTypes()->find($data['ticket_type_id']);
        
        return DB::transaction(function () use ($event, $data, $ticketType) {
            $quantity = $data['quantity'] ?? 1;
            $totalAmount = $ticketType ? $ticketType->price * $quantity : 0;

            $registration = $event->registrations()->create([
                'ticket_type_id' => $data['ticket_type_id'] ?? null,
                'user_id' => auth()->id(),
                'attendee_name' => $data['attendee_name'],
                'attendee_email' => $data['attendee_email'],
                'attendee_phone' => $data['attendee_phone'] ?? null,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
            ]);

            if ($ticketType) {
                $ticketType->increment('sold_count', $quantity);
            }

            return $registration;
        });
    }

    public function saveDraft(Event $event, array $data): Event
    {
        $data['status'] = 'draft';
        return $this->update($event, $data);
    }

    public function submitForReview(Event $event): Event
    {
        $event->update(['status' => 'pending_review']);
        return $event->fresh();
    }

    public function approve(Event $event): Event
    {
        $event->update(['status' => 'approved']);
        return $event->fresh();
    }

    public function reject(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $event->fresh();
    }

    public function publish(Event $event): Event
    {
        $event->update(['status' => 'published', 'published_at' => now()]);
        return $event->fresh();
    }

    public function schedule(Event $event, \DateTime $date): Event
    {
        $event->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $event->fresh();
    }

    public function unpublish(Event $event): Event
    {
        $event->update(['status' => 'unpublished']);
        return $event->fresh();
    }

    public function archive(Event $event): Event
    {
        $event->update(['status' => 'archived', 'archived_at' => now()]);
        return $event->fresh();
    }

    public function openRegistration(Event $event): Event
    {
        $event->update(['registration_status' => 'open']);
        return $event->fresh();
    }

    public function closeRegistration(Event $event): Event
    {
        $event->update(['registration_status' => 'closed']);
        return $event->fresh();
    }

    public function confirmPayment(EventRegistration $registration): EventRegistration
    {
        $registration->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        return $registration->fresh();
    }

    public function cancelRegistration(EventRegistration $registration, ?string $reason = null): EventRegistration
    {
        $registration->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
        if ($registration->ticketType) {
            $registration->ticketType->decrement('sold_count', $registration->quantity);
        }
        return $registration->fresh();
    }

    public function refundRegistration(EventRegistration $registration): EventRegistration
    {
        $registration->update(['payment_status' => 'refunded', 'refunded_at' => now()]);
        return $registration->fresh();
    }

    public function joinWaitlist(Event $event, array $data): EventRegistration
    {
        return $event->registrations()->create([...$data, 'status' => 'waitlist']);
    }

    public function promoteFromWaitlist(EventRegistration $registration): EventRegistration
    {
        $registration->update(['status' => 'confirmed']);
        return $registration->fresh();
    }

    public function addTicketType(Event $event, array $data): \Modules\Events\Domain\Models\EventTicketType
    {
        return $event->ticketTypes()->create($data);
    }

    public function updateTicketType(Event $event, string $ticketTypeId, array $data): \Modules\Events\Domain\Models\EventTicketType
    {
        $ticketType = $event->ticketTypes()->findOrFail($ticketTypeId);
        $ticketType->update($data);
        return $ticketType->fresh();
    }

    public function deleteTicketType(Event $event, string $ticketTypeId): bool
    {
        return $event->ticketTypes()->where('id', $ticketTypeId)->delete() > 0;
    }

    public function toggleTicketType(Event $event, string $ticketTypeId, bool $active): \Modules\Events\Domain\Models\EventTicketType
    {
        $ticketType = $event->ticketTypes()->findOrFail($ticketTypeId);
        $ticketType->update(['is_active' => $active]);
        return $ticketType->fresh();
    }

    public function addSession(Event $event, array $data): mixed
    {
        return $event->sessions()->create($data);
    }

    public function updateSession(Event $event, string $sessionId, array $data): mixed
    {
        $session = $event->sessions()->findOrFail($sessionId);
        $session->update($data);
        return $session->fresh();
    }

    public function deleteSession(Event $event, string $sessionId): bool
    {
        return $event->sessions()->where('id', $sessionId)->delete() > 0;
    }

    public function cancelSession(Event $event, string $sessionId): mixed
    {
        $session = $event->sessions()->findOrFail($sessionId);
        $session->update(['status' => 'cancelled']);
        return $session->fresh();
    }

    public function addSpeaker(Event $event, array $data): mixed
    {
        return $event->speakers()->create($data);
    }

    public function removeSpeaker(Event $event, string $speakerId): bool
    {
        return $event->speakers()->where('id', $speakerId)->delete() > 0;
    }

    public function sendSpeakerInvite(Event $event, string $speakerId): bool
    {
        // Queue speaker invitation email
        return true;
    }

    public function checkIn(EventRegistration $registration): EventRegistration
    {
        $registration->update(['checked_in_at' => now(), 'status' => 'attended']);
        return $registration->fresh();
    }

    public function startEvent(Event $event): Event
    {
        $event->update(['status' => 'ongoing', 'started_at' => now()]);
        return $event->fresh();
    }

    public function endEvent(Event $event): Event
    {
        $event->update(['status' => 'completed', 'ended_at' => now()]);
        return $event->fresh();
    }

    public function sendCertificates(Event $event): int
    {
        $attendees = $event->registrations()->where('status', 'attended')->get();
        // Queue certificate emails
        return $attendees->count();
    }

    public function publishRecordings(Event $event, array $recordings): Event
    {
        $event->update(['recordings' => $recordings, 'recordings_published_at' => now()]);
        return $event->fresh();
    }

    public function postponeEvent(Event $event, \DateTime $newDate): Event
    {
        $event->update([
            'status' => 'postponed',
            'start_date' => $newDate,
            'postponed_at' => now(),
        ]);
        return $event->fresh();
    }

    public function cancelEvent(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
        return $event->fresh();
    }

    public function duplicate(Event $event): Event
    {
        return DB::transaction(function () use ($event) {
            $clone = $event->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->save();

            foreach ($event->translations as $trans) {
                $clone->translations()->create($trans->only(['locale', 'title', 'slug', 'excerpt', 'description']));
            }

            foreach ($event->ticketTypes as $ticket) {
                $clone->ticketTypes()->create($ticket->only(['name', 'price', 'quantity', 'description']));
            }

            return $clone->fresh(['translations', 'ticketTypes']);
        });
    }
}
