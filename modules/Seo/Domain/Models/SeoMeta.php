<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SeoMeta Model - Stores SEO metadata for content.
 *
 * This model provides polymorphic SEO metadata storage
 * with support for meta tags, Open Graph, Twitter Cards, and schema markup.
 *
 * @property string $id UUID primary key
 * @property string $seoable_type Polymorphic model type
 * @property string $seoable_id UUID of the associated entity
 * @property string $locale Language code for this metadata
 * @property string|null $meta_title SEO page title
 * @property string|null $meta_description SEO meta description
 * @property string|null $meta_keywords SEO keywords (comma-separated)
 * @property string|null $canonical_url Canonical URL
 * @property string|null $robots Robots directive (index, noindex, etc.)
 * @property string|null $og_title Open Graph title
 * @property string|null $og_description Open Graph description
 * @property string|null $og_image Open Graph image URL
 * @property string|null $og_type Open Graph type
 * @property string|null $twitter_card Twitter card type
 * @property string|null $twitter_title Twitter card title
 * @property string|null $twitter_description Twitter card description
 * @property string|null $twitter_image Twitter card image URL
 * @property array|null $schema_markup JSON-LD schema markup
 * @property array|null $custom_meta Additional custom meta tags
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Model $seoable The associated entity (polymorphic)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SeoMeta forModel(string $type, string $id) Filter by entity
 * @method static \Illuminate\Database\Eloquent\Builder|SeoMeta locale(string $locale) Filter by locale
 * @method static \Illuminate\Database\Eloquent\Builder|SeoMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeoMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeoMeta query()
 */
class SeoMeta extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seo_metas';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'locale',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'custom_meta',
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'custom_meta' => 'array',
    ];

    /**
     * Get the entity this SEO metadata belongs to.
     *
     * @return MorphTo<Model, SeoMeta>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full page title with suffix.
     *
     * @param string|null $suffix Site name suffix
     * @return string The complete page title
     */
    public function getFullTitle(?string $suffix = null): string
    {
        $suffix = $suffix ?? config('seo.defaults.title_suffix');
        $separator = config('seo.defaults.title_separator', ' | ');
        
        return $this->meta_title 
            ? $this->meta_title . $separator . $suffix 
            : $suffix;
    }

    /**
     * Scope to filter metadata for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SeoMeta> $query
     * @param string $type The model class name
     * @param string $id The entity UUID
     * @return \Illuminate\Database\Eloquent\Builder<SeoMeta>
     */
    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('seoable_type', $type)->where('seoable_id', $id);
    }

    /**
     * Scope to filter metadata by locale.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SeoMeta> $query
     * @param string $locale The language code
     * @return \Illuminate\Database\Eloquent\Builder<SeoMeta>
     */
    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}
