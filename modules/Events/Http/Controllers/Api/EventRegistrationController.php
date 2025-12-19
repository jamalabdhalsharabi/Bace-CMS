<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Events\Application\Services\EventCommandService;
use Modules\Events\Application\Services\EventQueryService;
use Modules\Events\Http\Requests\AddToCalendarRequest;
use Modules\Events\Http\Requests\ConfirmationCodeRequest;
use Modules\Events\Http\Requests\RegisterEventRequest;
use Modules\Events\Http\Requests\SendReminderRequest;

/**
 * Event Registration Controller.
 *
 * Handles registration-related operations for events including
 * registration, cancellation, check-in, and reminders.
 *
 * @package Modules\Events\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventRegistrationController extends BaseController
{
    /**
     * Create a new EventRegistrationController instance.
     *
     * @param EventQueryService $queryService Service for event read operations
     * @param EventCommandService $commandService Service for event write operations
     */
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService
    ) {}

    /**
     * Register a user for the event.
     *
     * @param RegisterEventRequest $request The validated registration request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Registration confirmation or 404 error
     */
    public function register(RegisterEventRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $registration = $this->commandService->register($event, $request->validated());

            return $this->created(
                ['confirmation_code' => $registration->confirmation_code],
                'Registration successful'
            );
        } catch (\Throwable $e) {
            return $this->error('Failed to register: ' . $e->getMessage());
        }
    }

    /**
     * Cancel registration.
     *
     * @param ConfirmationCodeRequest $request The request with confirmation code
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Success message or 404 error
     */
    public function cancelRegistration(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->cancelRegistration($event, $request->confirmation_code);

            return $this->success(null, 'Registration cancelled');
        } catch (\Throwable $e) {
            return $this->error('Failed to cancel registration: ' . $e->getMessage());
        }
    }

    /**
     * Confirm attendance.
     *
     * @param ConfirmationCodeRequest $request The request with confirmation code
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Success message or 404 error
     */
    public function confirmAttendance(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->confirmAttendance($event, $request->confirmation_code);

            return $this->success(null, 'Attendance confirmed');
        } catch (\Throwable $e) {
            return $this->error('Failed to confirm attendance: ' . $e->getMessage());
        }
    }

    /**
     * Check-in attendee.
     *
     * @param ConfirmationCodeRequest $request The request with confirmation code
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Success message or 404 error
     */
    public function checkIn(ConfirmationCodeRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->checkIn($event, $request->confirmation_code);

            return $this->success(null, 'Check-in successful');
        } catch (\Throwable $e) {
            return $this->error('Failed to check-in: ' . $e->getMessage());
        }
    }

    /**
     * Get registrations.
     *
     * @param Request $request The incoming HTTP request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Paginated registrations or 404 error
     */
    public function registrations(Request $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $registrations = $this->queryService->getRegistrations(
                $event,
                $request->integer('per_page', 20)
            );

            return $this->paginated($registrations);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve registrations: ' . $e->getMessage());
        }
    }

    /**
     * Export registrations.
     *
     * @param Request $request The incoming HTTP request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Export result or 404 error
     */
    public function exportRegistrations(Request $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $result = $this->commandService->exportRegistrations(
                $event,
                $request->input('format', 'csv')
            );

            return $this->success($result);
        } catch (\Throwable $e) {
            return $this->error('Failed to export registrations: ' . $e->getMessage());
        }
    }

    /**
     * Send reminder to registrants.
     *
     * @param SendReminderRequest $request The validated reminder request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Success message or 404 error
     */
    public function sendReminder(SendReminderRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $this->commandService->sendReminder($event, $request->validated()['message'] ?? null);

            return $this->success(null, 'Reminders sent');
        } catch (\Throwable $e) {
            return $this->error('Failed to send reminders: ' . $e->getMessage());
        }
    }

    /**
     * Add to calendar.
     *
     * @param AddToCalendarRequest $request The validated calendar request
     * @param string $id The UUID of the event
     *
     * @return JsonResponse Calendar data or 404 error
     */
    public function addToCalendar(AddToCalendarRequest $request, string $id): JsonResponse
    {
        try {
            $event = $this->queryService->find($id);

            if (!$event) {
                return $this->notFound('Event not found');
            }

            $calendarData = $this->commandService->generateCalendarData(
                $event,
                $request->validated()['format'] ?? 'ics'
            );

            return $this->success($calendarData);
        } catch (\Throwable $e) {
            return $this->error('Failed to generate calendar data: ' . $e->getMessage());
        }
    }
}
