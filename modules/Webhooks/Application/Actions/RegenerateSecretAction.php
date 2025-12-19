<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Webhooks\Domain\Models\Webhook;

/**
 * Regenerate Secret Action.
 *
 * Generates a new secret key for a webhook.
 *
 * @package Modules\Webhooks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RegenerateSecretAction extends Action
{
    /**
     * Execute the regenerate secret action.
     *
     * @param Webhook $webhook The webhook to regenerate secret for
     *
     * @return Webhook The updated webhook
     */
    public function execute(Webhook $webhook): Webhook
    {
        $webhook->update(['secret' => Str::random(32)]);
        return $webhook->fresh();
    }
}
