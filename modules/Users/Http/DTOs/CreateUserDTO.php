<?php

declare(strict_types=1);

namespace Modules\Users\Http\DTOs;

readonly class CreateUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
        public string $status = 'active',
        public ?string $locale = null,
        public ?string $timezone = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            phone: $data['phone'] ?? null,
            status: $data['status'] ?? 'active',
            locale: $data['locale'] ?? null,
            timezone: $data['timezone'] ?? null,
        );
    }

    public function toUserArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
        ];
    }

    public function toProfileArray(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ], fn ($v) => $v !== null);
    }
}
