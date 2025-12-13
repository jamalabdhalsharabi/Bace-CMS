<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

final class RateAlertData extends DataTransferObject
{
    public function __construct(
        public readonly string $base_currency_id,
        public readonly string $target_currency_id,
        public readonly string $condition,
        public readonly float $threshold,
        public readonly ?string $user_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            base_currency_id: $data['base_currency_id'],
            target_currency_id: $data['target_currency_id'],
            condition: $data['condition'],
            threshold: (float) $data['threshold'],
            user_id: $data['user_id'] ?? null,
        );
    }
}
