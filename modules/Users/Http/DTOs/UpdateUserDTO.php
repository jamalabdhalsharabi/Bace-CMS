<?php

declare(strict_types=1);

namespace Modules\Users\Http\DTOs;

readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
        public ?string $status = null,
        public ?string $locale = null,
        public ?string $timezone = null,
        public ?string $bio = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            phone: $data['phone'] ?? null,
            status: $data['status'] ?? null,
            locale: $data['locale'] ?? null,
            timezone: $data['timezone'] ?? null,
            bio: $data['bio'] ?? null,
        );
    }

    public function toUserArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }

    public function toProfileArray(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'bio' => $this->bio,
        ], fn ($v) => $v !== null);
    }
}
