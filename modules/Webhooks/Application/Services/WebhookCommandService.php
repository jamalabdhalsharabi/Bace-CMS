<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Services;

use Modules\Webhooks\Application\Actions\CleanOldLogsAction;
use Modules\Webhooks\Application\Actions\CreateWebhookAction;
use Modules\Webhooks\Application\Actions\DeleteWebhookAction;
use Modules\Webhooks\Application\Actions\DispatchWebhookAction;
use Modules\Webhooks\Application\Actions\RegenerateSecretAction;
use Modules\Webhooks\Application\Actions\ResetFailureCountAction;
use Modules\Webhooks\Application\Actions\UpdateWebhookAction;
use Modules\Webhooks\Domain\Models\Webhook;

/**
 * Webhook Command Service.
 *
 * Orchestrates all webhook write operations via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Webhooks\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class WebhookCommandService
{
    /**
     * Create a new WebhookCommandService instance.
     *
     * @param CreateWebhookAction $createAction Action for creating webhooks
     * @param UpdateWebhookAction $updateAction Action for updating webhooks
     * @param DeleteWebhookAction $deleteAction Action for deleting webhooks
     * @param DispatchWebhookAction $dispatchAction Action for dispatching webhooks
     * @param CleanOldLogsAction $cleanAction Action for cleaning old logs
     * @param RegenerateSecretAction $regenerateAction Action for regenerating secrets
     * @param ResetFailureCountAction $resetAction Action for resetting failure counts
     */
    public function __construct(
        private readonly CreateWebhookAction $createAction,
        private readonly UpdateWebhookAction $updateAction,
        private readonly DeleteWebhookAction $deleteAction,
        private readonly DispatchWebhookAction $dispatchAction,
        private readonly CleanOldLogsAction $cleanAction,
        private readonly RegenerateSecretAction $regenerateAction,
        private readonly ResetFailureCountAction $resetAction,
    ) {}

    /**
     * Create a new webhook.
     *
     * @param array<string, mixed> $data The webhook data
     *
     * @return Webhook The created webhook
     */
    public function create(array $data): Webhook
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing webhook.
     *
     * @param Webhook $webhook The webhook to update
     * @param array<string, mixed> $data The updated data
     *
     * @return Webhook The updated webhook
     */
    public function update(Webhook $webhook, array $data): Webhook
    {
        return $this->updateAction->execute($webhook, $data);
    }

    /**
     * Delete a webhook.
     *
     * @param Webhook $webhook The webhook to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Webhook $webhook): bool
    {
        return $this->deleteAction->execute($webhook);
    }

    /**
     * Dispatch webhooks for an event.
     *
     * @param string $event The event name
     * @param array<string, mixed> $payload The event payload
     *
     * @return int Number of webhooks dispatched
     */
    public function dispatch(string $event, array $payload): int
    {
        return $this->dispatchAction->execute($event, $payload);
    }

    /**
     * Clean old webhook logs.
     *
     * @param int $days Number of days to keep logs
     *
     * @return int Number of deleted records
     */
    public function cleanOldLogs(int $days = 30): int
    {
        return $this->cleanAction->execute($days);
    }

    /**
     * Regenerate webhook secret.
     *
     * @param Webhook $webhook The webhook to regenerate secret for
     *
     * @return Webhook The updated webhook
     */
    public function regenerateSecret(Webhook $webhook): Webhook
    {
        return $this->regenerateAction->execute($webhook);
    }

    /**
     * Reset webhook failure count.
     *
     * @param Webhook $webhook The webhook to reset
     *
     * @return Webhook The updated webhook
     */
    public function resetFailureCount(Webhook $webhook): Webhook
    {
        return $this->resetAction->execute($webhook);
    }
}
