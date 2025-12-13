<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EventTranslation
 *
 * Eloquent model representing an event translation
 * for multi-language support.
 *
 * @package Modules\Events\Domain\Models
 *
 * @property string $id
 * @property string $event_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 *
 * @property-read Event $event
 */
class EventTranslation extends Model
{
    use HasUuids;

    protected $table = 'event_translations';

    protected $fillable = [
        'event_id', 'locale', 'title', 'slug', 'excerpt', 'description',
        'meta_title', 'meta_description',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
