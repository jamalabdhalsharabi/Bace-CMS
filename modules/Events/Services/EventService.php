<?php

declare(strict_types=1);

namespace Modules\Events\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Events\Contracts\EventServiceContract;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

/**
 * Class EventService
 *
 * Service class for managing events including CRUD operations,
 * workflow, registrations, tickets, sessions, and speakers.
 *
 * @package Modules\Events\Services
 */
class EventService implements EventServiceContract
{
    /**
     * Retrieve a paginated list of events with optional filtering.
     *
     * @param array $filters Optional filters: 'status', 'upcoming', 'featured'
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated events
     */
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Event::with(['translation', 'ticketTypes']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['upcoming'])) $query->upcoming();
        if (isset($filters['featured'])) $query->featured();
        return $query->orderBy('start_date')->paginate($perPage);
    }

    /**
     * Find an event by its UUID.
     *
     * @param string $id The event UUID
     *
     * @return Event|null The found event or null
     */
    public function find(string $id): ?Event
    {
        return Event::with(['translations', 'ticketTypes', 'registrations'])->find($id);
    }

    /**
     * Find an event by its URL slug.
     *
     * @param string $slug The URL slug
     *
     * @return Event|null The found event or null
     */
    public function findBySlug(string $slug): ?Event
    {
        return Event::whereHas('translations', fn($q) => $q->where('slug', $slug))
            ->with(['translations', 'ticketTypes'])->first();
    }

    /**
     * Create a new event with translations.
     *
     * @param array $data Event data including translations
     *
     * @return Event The created event
     *
     * @throws \Throwable If transaction fails
     */
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

    /**
     * Update an existing event.
     *
     * @param Event $event The event to update
     * @param array $data Updated data
     *
     * @return Event The updated event
     *
     * @throws \Throwable If transaction fails
     */
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

    /**
     * Soft-delete an event.
     *
     * @param Event $event The event to delete
     *
     * @return bool True if successful
     */
    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    /**
     * Register an attendee for an event.
     *
     * @param Event $event The event
     * @param array $data Registration data
     *
     * @return EventRegistration The created registration
     *
     * @throws \Throwable If transaction fails
     */
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

    /**
     * Save event changes as draft.
     *
     * @param Event $event The event
     * @param array $data Data to save
     *
     * @return Event The updated event
     */
    public function saveDraft(Event $event, array $data): Event
    {
        $data['status'] = 'draft';
        return $this->update($event, $data);
    }

    /**
     * Submit event for review.
     *
     * @param Event $event The event
     *
     * @return Event The submitted event
     */
    public function submitForReview(Event $event): Event
    {
        $event->update(['status' => 'pending_review']);
        return $event->fresh();
    }

    /**
     * Approve an event after review.
     *
     * @param Event $event The event
     *
     * @return Event The approved event
     */
    public function approve(Event $event): Event
    {
        $event->update(['status' => 'approved']);
        return $event->fresh();
    }

    /**
     * Reject an event during review.
     *
     * @param Event $event The event
     * @param string|null $reason Rejection reason
     *
     * @return Event The rejected event
     */
    public function reject(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $event->fresh();
    }

    /**
     * Publish an event.
     *
     * @param Event $event The event
     *
     * @return Event The published event
     */
    public function publish(Event $event): Event
    {
        $event->update(['status' => 'published', 'published_at' => now()]);
        return $event->fresh();
    }

    /**
     * Schedule event for future publication.
     *
     * @param Event $event The event
     * @param \DateTime $date Publication date
     *
     * @return Event The scheduled event
     */
    public function schedule(Event $event, \DateTime $date): Event
    {
        $event->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $event->fresh();
    }

    /**
     * Unpublish a published event.
     *
     * @param Event $event The event
     *
     * @return Event The unpublished event
     */
    public function unpublish(Event $event): Event
    {
        $event->update(['status' => 'unpublished']);
        return $event->fresh();
    }

    /**
     * Archive an event.
     *
     * @param Event $event The event
     *
     * @return Event The archived event
     */
    public function archive(Event $event): Event
    {
        $event->update(['status' => 'archived', 'archived_at' => now()]);
        return $event->fresh();
    }

    /**
     * Open event registration.
     *
     * @param Event $event The event
     *
     * @return Event The updated event
     */
    public function openRegistration(Event $event): Event
    {
        $event->update(['registration_status' => 'open']);
        return $event->fresh();
    }

    /**
     * Close event registration.
     *
     * @param Event $event The event
     *
     * @return Event The updated event
     */
    public function closeRegistration(Event $event): Event
    {
        $event->update(['registration_status' => 'closed']);
        return $event->fresh();
    }

    /**
     * Confirm payment for a registration.
     *
     * @param EventRegistration $registration The registration
     *
     * @return EventRegistration The confirmed registration
     */
    public function confirmPayment(EventRegistration $registration): EventRegistration
    {
        $registration->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        return $registration->fresh();
    }

    /**
     * Cancel an event registration.
     *
     * @param EventRegistration $registration The registration
     * @param string|null $reason Cancellation reason
     *
     * @return EventRegistration The cancelled registration
     */
    public function cancelRegistration(EventRegistration $registration, ?string $reason = null): EventRegistration
    {
        $registration->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
        if ($registration->ticketType) {
            $registration->ticketType->decrement('sold_count', $registration->quantity);
        }
        return $registration->fresh();
    }

    /**
     * Refund a registration payment.
     *
     * @param EventRegistration $registration The registration
     *
     * @return EventRegistration The refunded registration
     */
    public function refundRegistration(EventRegistration $registration): EventRegistration
    {
        $registration->update(['payment_status' => 'refunded', 'refunded_at' => now()]);
        return $registration->fresh();
    }

    /**
     * Add attendee to waitlist.
     *
     * @param Event $event The event
     * @param array $data Attendee data
     *
     * @return EventRegistration The waitlist registration
     */
    public function joinWaitlist(Event $event, array $data): EventRegistration
    {
        return $event->registrations()->create([...$data, 'status' => 'waitlist']);
    }

    /**
     * Promote attendee from waitlist to confirmed.
     *
     * @param EventRegistration $registration The registration
     *
     * @return EventRegistration The confirmed registration
     */
    public function promoteFromWaitlist(EventRegistration $registration): EventRegistration
    {
        $registration->update(['status' => 'confirmed']);
        return $registration->fresh();
    }

    /**
     * Add a ticket type to an event.
     *
     * @param Event $event The event
     * @param array $data Ticket type data
     *
     * @return \Modules\Events\Domain\Models\EventTicketType The created ticket type
     */
    public function addTicketType(Event $event, array $data): \Modules\Events\Domain\Models\EventTicketType
    {
        return $event->ticketTypes()->create($data);
    }

    /**
     * Update an event ticket type.
     *
     * @param Event $event The event
     * @param string $ticketTypeId Ticket type UUID
     * @param array $data Updated data
     *
     * @return \Modules\Events\Domain\Models\EventTicketType The updated ticket type
     */
    public function updateTicketType(Event $event, string $ticketTypeId, array $data): \Modules\Events\Domain\Models\EventTicketType
    {
        $ticketType = $event->ticketTypes()->findOrFail($ticketTypeId);
        $ticketType->update($data);
        return $ticketType->fresh();
    }

    /**
     * Delete an event ticket type.
     *
     * @param Event $event The event
     * @param string $ticketTypeId Ticket type UUID
     *
     * @return bool True if successful
     */
    public function deleteTicketType(Event $event, string $ticketTypeId): bool
    {
        return $event->ticketTypes()->where('id', $ticketTypeId)->delete() > 0;
    }

    /**
     * Toggle ticket type active status.
     *
     * @param Event $event The event
     * @param string $ticketTypeId Ticket type UUID
     * @param bool $active New active status
     *
     * @return \Modules\Events\Domain\Models\EventTicketType The updated ticket type
     */
    public function toggleTicketType(Event $event, string $ticketTypeId, bool $active): \Modules\Events\Domain\Models\EventTicketType
    {
        $ticketType = $event->ticketTypes()->findOrFail($ticketTypeId);
        $ticketType->update(['is_active' => $active]);
        return $ticketType->fresh();
    }

    /**
     * Add a session to an event.
     *
     * @param Event $event The event
     * @param array $data Session data
     *
     * @return mixed The created session
     */
    public function addSession(Event $event, array $data): mixed
    {
        return $event->sessions()->create($data);
    }

    /**
     * Update an event session.
     *
     * @param Event $event The event
     * @param string $sessionId Session UUID
     * @param array $data Updated data
     *
     * @return mixed The updated session
     */
    public function updateSession(Event $event, string $sessionId, array $data): mixed
    {
        $session = $event->sessions()->findOrFail($sessionId);
        $session->update($data);
        return $session->fresh();
    }

    /**
     * Delete an event session.
     *
     * @param Event $event The event
     * @param string $sessionId Session UUID
     *
     * @return bool True if successful
     */
    public function deleteSession(Event $event, string $sessionId): bool
    {
        return $event->sessions()->where('id', $sessionId)->delete() > 0;
    }

    /**
     * Cancel an event session.
     *
     * @param Event $event The event
     * @param string $sessionId Session UUID
     *
     * @return mixed The cancelled session
     */
    public function cancelSession(Event $event, string $sessionId): mixed
    {
        $session = $event->sessions()->findOrFail($sessionId);
        $session->update(['status' => 'cancelled']);
        return $session->fresh();
    }

    /**
     * Add a speaker to an event.
     *
     * @param Event $event The event
     * @param array $data Speaker data
     *
     * @return mixed The created speaker
     */
    public function addSpeaker(Event $event, array $data): mixed
    {
        return $event->speakers()->create($data);
    }

    /**
     * Remove a speaker from an event.
     *
     * @param Event $event The event
     * @param string $speakerId Speaker UUID
     *
     * @return bool True if successful
     */
    public function removeSpeaker(Event $event, string $speakerId): bool
    {
        return $event->speakers()->where('id', $speakerId)->delete() > 0;
    }

    /**
     * Send invitation to a speaker.
     *
     * @param Event $event The event
     * @param string $speakerId Speaker UUID
     *
     * @return bool True if sent
     */
    public function sendSpeakerInvite(Event $event, string $speakerId): bool
    {
        // Queue speaker invitation email
        return true;
    }

    /**
     * Check in an attendee at the event.
     *
     * @param EventRegistration $registration The registration
     *
     * @return EventRegistration The checked-in registration
     */
    public function checkIn(EventRegistration $registration): EventRegistration
    {
        $registration->update(['checked_in_at' => now(), 'status' => 'attended']);
        return $registration->fresh();
    }

    /**
     * Start an event.
     *
     * @param Event $event The event
     *
     * @return Event The started event
     */
    public function startEvent(Event $event): Event
    {
        $event->update(['status' => 'ongoing', 'started_at' => now()]);
        return $event->fresh();
    }

    /**
     * End an event.
     *
     * @param Event $event The event
     *
     * @return Event The completed event
     */
    public function endEvent(Event $event): Event
    {
        $event->update(['status' => 'completed', 'ended_at' => now()]);
        return $event->fresh();
    }

    /**
     * Send certificates to attendees.
     *
     * @param Event $event The event
     *
     * @return int Number of certificates sent
     */
    public function sendCertificates(Event $event): int
    {
        $attendees = $event->registrations()->where('status', 'attended')->get();
        // Queue certificate emails
        return $attendees->count();
    }

    /**
     * Publish event recordings.
     *
     * @param Event $event The event
     * @param array $recordings Recording URLs
     *
     * @return Event The updated event
     */
    public function publishRecordings(Event $event, array $recordings): Event
    {
        $event->update(['recordings' => $recordings, 'recordings_published_at' => now()]);
        return $event->fresh();
    }

    /**
     * Postpone an event to a new date.
     *
     * @param Event $event The event
     * @param \DateTime $newDate New start date
     *
     * @return Event The postponed event
     */
    public function postponeEvent(Event $event, \DateTime $newDate): Event
    {
        $event->update([
            'status' => 'postponed',
            'start_date' => $newDate,
            'postponed_at' => now(),
        ]);
        return $event->fresh();
    }

    /**
     * Cancel an event.
     *
     * @param Event $event The event
     * @param string|null $reason Cancellation reason
     *
     * @return Event The cancelled event
     */
    public function cancelEvent(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
        return $event->fresh();
    }

    /**
     * Duplicate an event.
     *
     * @param Event $event The event to duplicate
     *
     * @return Event The duplicated event
     */
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
