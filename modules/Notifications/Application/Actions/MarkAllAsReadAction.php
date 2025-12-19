<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Notifications\Domain\Models\Notification;

/**
 * Mark All As Read Action.
 *
 * Marks all unread notifications as read for a user.
 *
 * @package Modules\Notifications\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MarkAllAsReadAction extends Action
{
    /**
     * Execute the mark all as read action.
     *
     * @param string $userId The user ID
     *
     * @return int Number of notifications marked as read
     */
    public function execute(string $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
