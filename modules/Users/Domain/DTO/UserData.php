<?php

declare(strict_types=1);

namespace Modules\Users\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * User Data Transfer Object.
 */
final class UserData extends DataTransferObject
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly string $status = 'active',
        public readonly ?string $first_name = null,
        public readonly ?string $last_name = null,
        public readonly ?string $phone = null,
        public readonly ?string $bio = null,
        public readonly ?string $avatar = null,
        public readonly ?array $role_ids = null,
    ) {}

    public function toUserArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }

    public function toProfileArray(): array
    {
        return array_filter([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'bio' => $this->bio,
        ], fn ($v) => $v !== null);
    }
}
