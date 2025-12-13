<?php

declare(strict_types=1);

namespace Modules\Core\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Status Value Object.
 *
 * Immutable value object representing an entity status.
 */
final readonly class Status
{
    public const DRAFT = 'draft';
    public const PENDING_REVIEW = 'pending_review';
    public const IN_REVIEW = 'in_review';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const PUBLISHED = 'published';
    public const SCHEDULED = 'scheduled';
    public const ARCHIVED = 'archived';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PENDING_REVIEW,
        self::IN_REVIEW,
        self::APPROVED,
        self::REJECTED,
        self::PUBLISHED,
        self::SCHEDULED,
        self::ARCHIVED,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$value}");
        }
        $this->value = $value;
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
