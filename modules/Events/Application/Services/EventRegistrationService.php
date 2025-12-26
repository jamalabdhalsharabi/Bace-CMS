<?php

declare(strict_types=1);

namespace Modules\Events\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Events\Domain\Events\RegistrationReceived;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

/**
 * Event Registration Service.
 *
 * Manages event registrations and attendees.
 * Single Responsibility: Registration operations.
 */
final class EventRegistrationService
{
    /**
     * Register attendee for an event.
     */
    public function register(Event $event, array $data): EventRegistration
    {
        return DB::transaction(function () use ($event, $data) {
            $registration = $event->registrations()->create([
                'user_id' => $data['user_id'] ?? request()->user()?->id,
                'ticket_type_id' => $data['ticket_type_id'] ?? null,
                'quantity' => $data['quantity'] ?? 1,
                'status' => 'pending',
                'attendee_name' => $data['attendee_name'],
                'attendee_email' => $data['attendee_email'],
                'attendee_phone' => $data['attendee_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($registration->ticketType) {
                $registration->ticketType->increment('sold_count', $registration->quantity);
            }

            event(new RegistrationReceived($event, $registration));

            return $registration;
        });
    }

    /**
     * Confirm a registration.
     */
    public function confirm(EventRegistration $registration): EventRegistration
    {
        $registration->update(['status' => 'confirmed']);

        return $registration->fresh();
    }

    /**
     * Cancel a registration.
     */
    public function cancel(EventRegistration $registration, ?string $reason = null): EventRegistration
    {
        $registration->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        if ($registration->ticketType) {
            $registration->ticketType->decrement('sold_count', $registration->quantity);
        }

        return $registration->fresh();
    }

    /**
     * Check-in an attendee.
     */
    public function checkIn(EventRegistration $registration): EventRegistration
    {
        $registration->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return $registration->fresh();
    }

    /**
     * Add to waitlist.
     */
    public function addToWaitlist(Event $event, array $data): EventRegistration
    {
        return $event->registrations()->create([
            'user_id' => $data['user_id'] ?? request()->user()?->id,
            'status' => 'waitlist',
            'attendee_name' => $data['attendee_name'],
            'attendee_email' => $data['attendee_email'],
        ]);
    }

    /**
     * Get registration count for an event.
     */
    public function getRegistrationCount(Event $event): int
    {
        return $event->registrations()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->sum('quantity');
    }

    /**
     * Check if event has available spots.
     */
    public function hasAvailableSpots(Event $event): bool
    {
        if (!$event->max_attendees) {
            return true;
        }

        return $this->getRegistrationCount($event) < $event->max_attendees;
    }
}
