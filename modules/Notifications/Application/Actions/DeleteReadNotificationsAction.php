<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Delete Read Notifications Action.
 *
 * Deletes all read notifications for a user.
 *
 * @package Modules\Notifications\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeleteReadNotificationsAction extends Action
{
    /**
     * Execute the delete read notifications action.
     *
     * @param string $userId The user ID
     *
     * @return int Number of notifications deleted
     */
    public function execute(string $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNotNull('read_at')
            ->delete();
    }
}
