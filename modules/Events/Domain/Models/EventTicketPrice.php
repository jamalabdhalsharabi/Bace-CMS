<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventTicketPrice Model - Stores ticket prices in different currencies.
 *
 * This model manages multi-currency pricing for event ticket types
 * with support for compare/original prices for promotions.
 *
 * @property string $id UUID primary key
 * @property string $ticket_type_id Foreign key to event_ticket_types table
 * @property string $currency_id Foreign key to currencies table
 * @property float $amount Current ticket price
 * @property float|null $compare_amount Original/compare price for discounts
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read EventTicketType $ticketType Parent ticket type
 * @property-read \Modules\Currency\Domain\Models\Currency $currency Price currency
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketPrice query()
 */
class EventTicketPrice extends Model
{
    use HasUuids;

    protected $table = 'event_ticket_prices';

    protected $fillable = [
        'ticket_type_id',
        'currency_id',
        'amount',
        'compare_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'compare_amount' => 'decimal:4',
    ];

    /**
     * Get the ticket type this price belongs to.
     *
     * @return BelongsTo<EventTicketType, EventTicketPrice>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class, 'ticket_type_id');
    }

    /**
     * Get the currency for this price.
     *
     * @return BelongsTo<\Modules\Currency\Domain\Models\Currency, EventTicketPrice>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(\Modules\Currency\Domain\Models\Currency::class);
    }

    /**
     * Check if this price has a discount (compare price is higher).
     *
     * @return bool True if compare_amount exists and is greater than amount
     */
    public function hasDiscount(): bool
    {
        return $this->compare_amount !== null && $this->compare_amount > $this->amount;
    }

    /**
     * Get the discount percentage.
     *
     * @return float Discount percentage (0-100)
     */
    public function getDiscountPercentage(): float
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round((($this->compare_amount - $this->amount) / $this->compare_amount) * 100, 2);
    }
}
