<?php

declare(strict_types=1);

namespace Modules\Services\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTranslation extends Model
{
    use HasUuids;

    protected $table = 'service_translations';

    protected $fillable = [
        'service_id', 'locale', 'name', 'slug', 'short_description',
        'description', 'features', 'benefits', 'process', 'faq',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected $casts = [
        'features' => 'array',
        'benefits' => 'array',
        'process' => 'array',
        'faq' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
