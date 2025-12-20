<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Login Data Transfer Object.
 *
 * Encapsulates user login credentials for authentication requests.
 * Provides immutable data structure for passing login information
 * between application layers following Clean Architecture principles.
 *
 * @package Modules\Auth\Domain\DTO
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class LoginData extends DataTransferObject
{
    /**
     * Create a new LoginData instance.
     *
     * @param string $email User email address for authentication
     * @param string $password User password for authentication
     * @param bool $remember Whether to remember the user's login session
     */
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
    ) {}
}
