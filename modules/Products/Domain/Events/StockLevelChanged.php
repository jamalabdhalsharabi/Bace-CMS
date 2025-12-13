<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Products\Domain\Models\Product;

/**
 * Stock Level Changed Event.
 */
final class StockLevelChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly int $previousQuantity,
        public readonly int $newQuantity,
        public readonly string $reason,
    ) {}
}
