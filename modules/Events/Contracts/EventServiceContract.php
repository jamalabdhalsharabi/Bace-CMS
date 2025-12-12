<?php

declare(strict_types=1);

namespace Modules\Events\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Models\EventRegistration;

interface EventServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator;
    public function find(string $id): ?Event;
    public function findBySlug(string $slug): ?Event;
    public function create(array $data): Event;
    public function update(Event $event, array $data): Event;
    public function delete(Event $event): bool;

    // Workflow
    public function saveDraft(Event $event, array $data): Event;
    public function submitForReview(Event $event): Event;
    public function approve(Event $event): Event;
    public function reject(Event $event, ?string $reason = null): Event;
    public function publish(Event $event): Event;
    public function schedule(Event $event, \DateTime $date): Event;
    public function unpublish(Event $event): Event;
    public function archive(Event $event): Event;

    // Registration Management
    public function openRegistration(Event $event): Event;
    public function closeRegistration(Event $event): Event;
    public function register(Event $event, array $data): EventRegistration;
    public function confirmPayment(EventRegistration $registration): EventRegistration;
    public function cancelRegistration(EventRegistration $registration, ?string $reason = null): EventRegistration;
    public function refundRegistration(EventRegistration $registration): EventRegistration;
    public function joinWaitlist(Event $event, array $data): EventRegistration;
    public function promoteFromWaitlist(EventRegistration $registration): EventRegistration;

    // Ticket Types
    public function addTicketType(Event $event, array $data): \Modules\Events\Domain\Models\EventTicketType;
    public function updateTicketType(Event $event, string $ticketTypeId, array $data): \Modules\Events\Domain\Models\EventTicketType;
    public function deleteTicketType(Event $event, string $ticketTypeId): bool;
    public function toggleTicketType(Event $event, string $ticketTypeId, bool $active): \Modules\Events\Domain\Models\EventTicketType;

    // Sessions
    public function addSession(Event $event, array $data): mixed;
    public function updateSession(Event $event, string $sessionId, array $data): mixed;
    public function deleteSession(Event $event, string $sessionId): bool;
    public function cancelSession(Event $event, string $sessionId): mixed;

    // Speakers
    public function addSpeaker(Event $event, array $data): mixed;
    public function removeSpeaker(Event $event, string $speakerId): bool;
    public function sendSpeakerInvite(Event $event, string $speakerId): bool;

    // Event Lifecycle
    public function checkIn(EventRegistration $registration): EventRegistration;
    public function startEvent(Event $event): Event;
    public function endEvent(Event $event): Event;
    public function sendCertificates(Event $event): int;
    public function publishRecordings(Event $event, array $recordings): Event;

    // Other
    public function postponeEvent(Event $event, \DateTime $newDate): Event;
    public function cancelEvent(Event $event, ?string $reason = null): Event;
    public function duplicate(Event $event): Event;
}
