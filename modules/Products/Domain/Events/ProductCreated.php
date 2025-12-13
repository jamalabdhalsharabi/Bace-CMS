<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Products\Domain\Models\Product;

/**
 * Product Created Event.
 */
final class ProductCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Product $product
    ) {}
}
