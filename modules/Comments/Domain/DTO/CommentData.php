<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Comment Data Transfer Object.
 */
final class CommentData extends DataTransferObject
{
    public function __construct(
        public readonly string $commentable_type,
        public readonly string $commentable_id,
        public readonly string $content,
        public readonly ?string $parent_id = null,
        public readonly ?string $user_id = null,
        public readonly ?string $guest_name = null,
        public readonly ?string $guest_email = null,
    ) {}
}
