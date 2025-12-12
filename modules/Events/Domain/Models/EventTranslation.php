<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
