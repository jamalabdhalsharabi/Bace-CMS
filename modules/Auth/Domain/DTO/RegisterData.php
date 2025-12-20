<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Registration Data Transfer Object.
 *
 * Encapsulates user registration information for account creation requests.
 * Provides immutable data structure for passing registration data between
 * application layers following Clean Architecture principles.
 *
 * @package Modules\Auth\Domain\DTO
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RegisterData extends DataTransferObject
{
    /**
     * Create a new RegisterData instance.
     *
     * @param string $email User email address (must be unique)
     * @param string $password User password (will be hashed)
     * @param string|null $first_name Optional user first name
     * @param string|null $last_name Optional user last name
     * @param string|null $phone Optional user phone number
     */
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $first_name = null,
        public readonly ?string $last_name = null,
        public readonly ?string $phone = null,
    ) {}
}
