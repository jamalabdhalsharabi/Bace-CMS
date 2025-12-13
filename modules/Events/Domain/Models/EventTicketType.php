<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasTranslations;

/**
 * Class EventTicketType
 *
 * Eloquent model representing an event ticket type
 * with pricing, quantity limits, and availability.
 *
 * @package Modules\Events\Domain\Models
 *
 * @property string $id
 * @property string $event_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property string|null $currency_id
 * @property int|null $quantity
 * @property int $sold_count
 * @property int|null $max_per_order
 * @property \Carbon\Carbon|null $sale_start
 * @property \Carbon\Carbon|null $sale_end
 * @property bool $is_active
 * @property int $sort_order
 *
 * @property-read Event $event Parent event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventTicketTypeTranslation> $translations
 * @property-read EventTicketTypeTranslation|null $translation Current locale translation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketType query()
 */
class EventTicketType extends Model
{
    use HasUuids;
    use HasTranslations;

    public array $translatedAttributes = ['name', 'description'];
    public string $translationForeignKey = 'ticket_type_id';

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

    /**
     * Get the parent event.
     *
     * @return BelongsTo<Event, EventTicketType>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the available quantity of tickets.
     *
     * @return int Number of tickets still available
     */
    public function getAvailableQuantity(): int
    {
        return max(0, ($this->quantity ?? PHP_INT_MAX) - $this->sold_count);
    }

    /**
     * Check if this ticket type is currently available for purchase.
     *
     * Considers active status, sale period, and available quantity.
     *
     * @return bool True if tickets can be purchased
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->sale_start && $this->sale_start > now()) {
            return false;
        }
        if ($this->sale_end && $this->sale_end < now()) {
            return false;
        }

        return $this->getAvailableQuantity() > 0;
    }
}
