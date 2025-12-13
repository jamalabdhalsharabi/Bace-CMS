<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PageTranslation Model - Stores localized content for pages.
 *
 * This model holds translated content for pages including title, slug,
 * content body, and SEO metadata for each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $page_id Foreign key to pages table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $title Translated page title
 * @property string $slug URL-friendly slug for this locale
 * @property string|null $excerpt Short summary or teaser
 * @property string|null $content Full page content (HTML)
 * @property string|null $meta_title SEO meta title
 * @property string|null $meta_description SEO meta description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Page $page The parent page
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation query()
 */
class PageTranslation extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'page_translations';

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the page that owns this translation.
     *
     * @return BelongsTo<Page, PageTranslation>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
