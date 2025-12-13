<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Form Submission Data Transfer Object.
 */
final class SubmissionData extends DataTransferObject
{
    public function __construct(
        public readonly string $form_id,
        public readonly array $data,
        public readonly ?string $user_id = null,
        public readonly ?string $ip_address = null,
        public readonly ?string $user_agent = null,
        public readonly ?string $referrer = null,
    ) {}
}
