<?php

declare(strict_types=1);

namespace Modules\Events\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Event Data Transfer Object.
 */
final class EventData extends DataTransferObject
{
    public function __construct(
        public readonly string $status = 'draft',
        public readonly bool $is_featured = false,
        public readonly ?string $event_type = null,
        public readonly ?string $venue_name = null,
        public readonly ?string $venue_address = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly bool $is_online = false,
        public readonly ?string $online_url = null,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly string $timezone = 'UTC',
        public readonly ?int $max_attendees = null,
        public readonly ?string $registration_deadline = null,
        public readonly bool $is_free = false,
        public readonly ?string $featured_image_id = null,
        public readonly array $translations = [],
        public readonly ?array $meta = null,
    ) {}
}
