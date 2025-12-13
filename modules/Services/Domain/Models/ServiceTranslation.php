<?php

declare(strict_types=1);

namespace Modules\Services\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ServiceTranslation
 *
 * Eloquent model representing a service translation
 * for multi-language support with features and FAQ.
 *
 * @package Modules\Services\Domain\Models
 *
 * @property string $id
 * @property string $service_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $description
 * @property array|null $features
 * @property array|null $benefits
 * @property array|null $process
 * @property array|null $faq
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 *
 * @property-read Service $service
 */
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
