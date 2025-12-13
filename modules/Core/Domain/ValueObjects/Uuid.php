<?php

declare(strict_types=1);

namespace Modules\Core\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

/**
 * UUID Value Object.
 *
 * Immutable value object representing a UUID.
 */
final readonly class Uuid
{
    private string $value;

    public function __construct(string $value)
    {
        if (!RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID: {$value}");
        }
        $this->value = $value;
    }

    /**
     * Generate a new UUID.
     */
    public static function generate(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    /**
     * Create from string.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Get the string value.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Check equality.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
