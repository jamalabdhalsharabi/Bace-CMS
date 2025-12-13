<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Login Data Transfer Object.
 */
final class LoginData extends DataTransferObject
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
    ) {}
}
