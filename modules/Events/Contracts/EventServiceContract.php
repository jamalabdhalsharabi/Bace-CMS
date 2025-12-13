<?php

declare(strict_types=1);

namespace Modules\Events\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

/**
 * Interface EventServiceContract
 * 
 * Defines the contract for event management services.
 * Handles CRUD, workflow, registration, ticket types, sessions,
 * speakers, event lifecycle, and duplication.
 * 
 * @package Modules\Events\Contracts
 */
interface EventServiceContract
{
    /**
     * Get paginated list of events with optional filters.
     *
     * @param array $filters Filter criteria (status, date_range, type, etc.)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    /** @param string $id Event UUID @return Event|null */
    public function find(string $id): ?Event;

    /** @param string $slug Event slug @return Event|null */
    public function findBySlug(string $slug): ?Event;

    /** @param array $data Event data @return Event */
    public function create(array $data): Event;

    /** @param Event $event @param array $data @return Event */
    public function update(Event $event, array $data): Event;

    /** @param Event $event @return bool */
    public function delete(Event $event): bool;

    /** @param Event $event @param array $data @return Event */
    public function saveDraft(Event $event, array $data): Event;

    /** @param Event $event @return Event */
    public function submitForReview(Event $event): Event;

    /** @param Event $event @return Event */
    public function approve(Event $event): Event;

    /** @param Event $event @param string|null $reason @return Event */
    public function reject(Event $event, ?string $reason = null): Event;

    /** @param Event $event @return Event */
    public function publish(Event $event): Event;

    /** @param Event $event @param \DateTime $date @return Event */
    public function schedule(Event $event, \DateTime $date): Event;

    /** @param Event $event @return Event */
    public function unpublish(Event $event): Event;

    /** @param Event $event @return Event */
    public function archive(Event $event): Event;

    /** @param Event $event @return Event */
    public function openRegistration(Event $event): Event;

    /** @param Event $event @return Event */
    public function closeRegistration(Event $event): Event;

    /** @param Event $event @param array $data @return EventRegistration */
    public function register(Event $event, array $data): EventRegistration;

    /** @param EventRegistration $registration @return EventRegistration */
    public function confirmPayment(EventRegistration $registration): EventRegistration;

    /** @param EventRegistration $registration @param string|null $reason @return EventRegistration */
    public function cancelRegistration(EventRegistration $registration, ?string $reason = null): EventRegistration;

    /** @param EventRegistration $registration @return EventRegistration */
    public function refundRegistration(EventRegistration $registration): EventRegistration;

    /** @param Event $event @param array $data @return EventRegistration */
    public function joinWaitlist(Event $event, array $data): EventRegistration;

    /** @param EventRegistration $registration @return EventRegistration */
    public function promoteFromWaitlist(EventRegistration $registration): EventRegistration;

    /** @param Event $event @param array $data @return \Modules\Events\Domain\Models\EventTicketType */
    public function addTicketType(Event $event, array $data): \Modules\Events\Domain\Models\EventTicketType;

    /** @param Event $event @param string $ticketTypeId @param array $data @return \Modules\Events\Domain\Models\EventTicketType */
    public function updateTicketType(Event $event, string $ticketTypeId, array $data): \Modules\Events\Domain\Models\EventTicketType;

    /** @param Event $event @param string $ticketTypeId @return bool */
    public function deleteTicketType(Event $event, string $ticketTypeId): bool;

    /** @param Event $event @param string $ticketTypeId @param bool $active @return \Modules\Events\Domain\Models\EventTicketType */
    public function toggleTicketType(Event $event, string $ticketTypeId, bool $active): \Modules\Events\Domain\Models\EventTicketType;

    /** @param Event $event @param array $data @return mixed */
    public function addSession(Event $event, array $data): mixed;

    /** @param Event $event @param string $sessionId @param array $data @return mixed */
    public function updateSession(Event $event, string $sessionId, array $data): mixed;

    /** @param Event $event @param string $sessionId @return bool */
    public function deleteSession(Event $event, string $sessionId): bool;

    /** @param Event $event @param string $sessionId @return mixed */
    public function cancelSession(Event $event, string $sessionId): mixed;

    /** @param Event $event @param array $data @return mixed */
    public function addSpeaker(Event $event, array $data): mixed;

    /** @param Event $event @param string $speakerId @return bool */
    public function removeSpeaker(Event $event, string $speakerId): bool;

    /** @param Event $event @param string $speakerId @return bool */
    public function sendSpeakerInvite(Event $event, string $speakerId): bool;

    /** @param EventRegistration $registration @return EventRegistration */
    public function checkIn(EventRegistration $registration): EventRegistration;

    /** @param Event $event @return Event */
    public function startEvent(Event $event): Event;

    /** @param Event $event @return Event */
    public function endEvent(Event $event): Event;

    /** @param Event $event @return int Number of certificates sent */
    public function sendCertificates(Event $event): int;

    /** @param Event $event @param array $recordings @return Event */
    public function publishRecordings(Event $event, array $recordings): Event;

    /** @param Event $event @param \DateTime $newDate @return Event */
    public function postponeEvent(Event $event, \DateTime $newDate): Event;

    /** @param Event $event @param string|null $reason @return Event */
    public function cancelEvent(Event $event, ?string $reason = null): Event;

    /** @param Event $event @return Event */
    public function duplicate(Event $event): Event;
}
