<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Form Data Transfer Object.
 */
final class FormData extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly string $type = 'contact',
        public readonly array $success_message = ['en' => 'Thank you!'],
        public readonly array $notification_emails = [],
        public readonly ?string $redirect_url = null,
        public readonly bool $is_active = true,
        public readonly bool $captcha_enabled = false,
        public readonly array $fields = [],
        public readonly array $settings = [],
    ) {}
}
