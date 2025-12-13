<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ServiceTranslation Model - Stores localized content for services.
 *
 * This model holds translated content for services including title, slug,
 * description, content body, and SEO metadata for each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $service_id Foreign key to services table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $title Translated service title
 * @property string $slug URL-friendly slug for this locale
 * @property string|null $excerpt Short summary or teaser
 * @property string|null $description Service description
 * @property string|null $content Full service content (HTML)
 * @property string|null $meta_title SEO meta title
 * @property string|null $meta_description SEO meta description
 * @property string|null $meta_keywords SEO keywords (comma-separated)
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Service $service The parent service
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTranslation query()
 */
class ServiceTranslation extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_translations';

    protected $fillable = [
        'service_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Get the service that owns this translation.
     *
     * @return BelongsTo<Service, ServiceTranslation>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
