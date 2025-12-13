<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Register Data Transfer Object.
 */
final class RegisterData extends DataTransferObject
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $first_name = null,
        public readonly ?string $last_name = null,
        public readonly ?string $phone = null,
    ) {}
}
