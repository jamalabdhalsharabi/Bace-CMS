<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTicketType extends Model
{
    use HasUuids;

    protected $table = 'event_ticket_types';

    protected $fillable = [
        'event_id', 'name', 'description', 'price', 'currency_id',
        'quantity', 'sold_count', 'max_per_order', 'sale_start', 'sale_end',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'sold_count' => 'integer',
        'max_per_order' => 'integer',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getAvailableQuantity(): int
    {
        return max(0, ($this->quantity ?? PHP_INT_MAX) - $this->sold_count);
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) return false;
        if ($this->sale_start && $this->sale_start > now()) return false;
        if ($this->sale_end && $this->sale_end < now()) return false;
        return $this->getAvailableQuantity() > 0;
    }
}
