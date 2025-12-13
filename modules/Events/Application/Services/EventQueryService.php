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
 * Handles read-only operations for events.
 */
final class EventQueryService
{
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
