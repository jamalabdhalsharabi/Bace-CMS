<?php

declare(strict_types=1);

namespace Modules\Events\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Domain\Models\Event;
use Modules\Events\Domain\Repositories\EventRepository;

/**
 * Event Query Service.
 *
 * Handles all read operations for events via Repository pattern.
 * No write operations - delegates to EventCommandService for mutations.
 *
 * @package Modules\Events\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class EventQueryService
{
    /**
     * Create a new EventQueryService instance.
     *
     * @param EventRepository $repository The event repository
     */
    public function __construct(
        private readonly EventRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation', 'ticketTypes'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Event
    {
        return $this->repository
            ->with(['translations', 'ticketTypes', 'registrations'])
            ->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Event
    {
        return $this->repository
            ->with(['translations', 'ticketTypes'])
            ->findBySlug($slug, $locale);
    }

    public function getUpcoming(int $limit = 10): Collection
    {
        return $this->repository
            ->with(['translation', 'ticketTypes'])
            ->getUpcoming($limit);
    }

    public function getOngoing(): Collection
    {
        return $this->repository
            ->with(['translation'])
            ->getOngoing();
    }

    public function getPast(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation'])
            ->getPast($perPage);
    }
}
