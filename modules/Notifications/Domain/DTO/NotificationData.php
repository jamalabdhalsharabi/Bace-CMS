<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Notification Data Transfer Object.
 */
final class NotificationData extends DataTransferObject
{
    public function __construct(
        public readonly string $user_id,
        public readonly string $type,
        public readonly array $data,
        public readonly ?string $notifiable_id = null,
        public readonly ?string $notifiable_type = null,
    ) {}
}
