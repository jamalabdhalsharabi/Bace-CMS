<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

final class ExchangeRateData extends DataTransferObject
{
    public function __construct(
        public readonly string $base_currency_id,
        public readonly string $target_currency_id,
        public readonly float $rate,
        public readonly ?float $inverse_rate = null,
        public readonly ?string $provider = 'manual',
        public readonly ?bool $is_frozen = false,
        public readonly ?\DateTimeInterface $valid_from = null,
        public readonly ?\DateTimeInterface $valid_until = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            base_currency_id: $data['base_currency_id'],
            target_currency_id: $data['target_currency_id'],
            rate: (float) $data['rate'],
            inverse_rate: isset($data['inverse_rate']) ? (float) $data['inverse_rate'] : null,
            provider: $data['provider'] ?? 'manual',
            is_frozen: $data['is_frozen'] ?? false,
            valid_from: isset($data['valid_from']) ? new \DateTime($data['valid_from']) : null,
            valid_until: isset($data['valid_until']) ? new \DateTime($data['valid_until']) : null,
        );
    }
}
