<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Webhooks\Domain\Models\Webhook;

/**
 * Webhook Repository Interface.
 *
 * Defines the contract for webhook data access operations.
 *
 * @extends RepositoryInterface<Webhook>
 *
 * @package Modules\Webhooks\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface WebhookRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated webhooks with optional filters.
     *
     * @param array<string, mixed> $filters Filter criteria
     * @param int                  $perPage Items per page
     *
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all active webhooks.
     *
     * @return Collection<int, Webhook>
     */
    public function getActive(): Collection;

    /**
     * Get active webhooks subscribed to a specific event.
     *
     * @param string $event Event name
     *
     * @return Collection<int, Webhook>
     */
    public function getActiveByEvent(string $event): Collection;

    /**
     * Get webhooks with high failure count.
     *
     * @param int $threshold Failure count threshold
     *
     * @return Collection<int, Webhook>
     */
    public function getFailingWebhooks(int $threshold = 5): Collection;

}
