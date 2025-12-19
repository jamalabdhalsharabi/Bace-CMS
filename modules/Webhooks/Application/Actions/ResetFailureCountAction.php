<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;

/**
 * Reset Failure Count Action.
 *
 * Resets the failure count for a webhook.
 *
 * @package Modules\Webhooks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ResetFailureCountAction extends Action
{
    /**
     * Execute the reset failure count action.
     *
     * @param Webhook $webhook The webhook to reset
     *
     * @return Webhook The updated webhook
     */
    public function execute(Webhook $webhook): Webhook
    {
        $webhook->update(['failure_count' => 0]);
        return $webhook->fresh();
    }
}
