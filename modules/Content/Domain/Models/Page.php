<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasMedia;
use Modules\Core\Traits\HasOrdering;
use Modules\Core\Traits\HasStatus;

/**
 * Class Page
 *
 * Eloquent model representing a CMS page with translations,
 * hierarchy, and publishing capabilities.
 *
 * @package Modules\Content\Domain\Models
 *
 * @property string $id
 * @property string|null $parent_id
 * @property string|null $author_id
 * @property string|null $featured_image_id
 * @property string|null $template
 * @property string $status
 * @property bool $is_homepage
 * @property int $ordering
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Page|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|Page[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|PageTranslation[] $translations
 * @property-read PageTranslation|null $translation
 * @property-read string|null $title
 * @property-read string|null $slug
 * @property-read string|null $content
 * @property-read string $full_slug
 * @property-read string $url
 */
class Page extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use HasStatus;
    use HasMedia;
    use HasOrdering;

    protected $table = 'pages';

    protected $fillable = [
        'parent_id',
        'author_id',
        'featured_image_id',
        'template',
        'status',
        'is_homepage',
        'ordering',
        'published_at',
    ];

    protected $casts = [
        'is_homepage' => 'boolean',
        'ordering' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Define the belongs-to relationship with the parent page.
     *
     * Retrieves the parent Page model for hierarchical navigation.
     * Returns null for top-level pages with no parent.
     *
     * @return BelongsTo The belongs-to relationship instance to Page
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Define the has-many relationship with child pages.
     *
     * Retrieves all direct child pages ordered by their ordering field.
     * Used for building navigation menus and page hierarchies.
     *
     * @return HasMany The has-many relationship instance to Page
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * Define the belongs-to relationship with the page author.
     *
     * Retrieves the User model who created or manages this page.
     * Used for attribution and permission checking.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    /**
     * Define the belongs-to relationship with the featured image.
     *
     * Retrieves the Media model representing the page's hero image
     * or thumbnail for social sharing and visual display.
     *
     * @return BelongsTo The belongs-to relationship instance to Media
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    /**
     * Define the has-many relationship with page translations.
     *
     * Retrieves all translation records for this page across
     * all supported locales including title, content, and SEO fields.
     *
     * @return HasMany The has-many relationship instance to PageTranslation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    /**
     * Define the has-one relationship with the current locale translation.
     *
     * Retrieves the translation record matching the application's
     * current locale setting for displaying localized content.
     *
     * @return HasOne The has-one relationship instance to PageTranslation
     */
    public function translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * Accessor for the page's localized title.
     *
     * Returns the title from the current locale translation if available,
     * otherwise falls back to the first available translation's title.
     *
     * @return string|null The localized title or null if no translations exist
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Accessor for the page's localized URL slug.
     *
     * Returns the slug from the current locale translation if available,
     * otherwise falls back to the first available translation's slug.
     *
     * @return string|null The localized slug or null if no translations exist
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Accessor for the page's localized content.
     *
     * Returns the HTML content from the current locale translation
     * for rendering the page body.
     *
     * @return string|null The localized content or null if not set
     */
    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

    /**
     * Accessor for the page's full hierarchical URL slug.
     *
     * Builds the complete URL path by traversing up the parent
     * hierarchy and concatenating all slugs with slashes.
     * Example: 'about/team/leadership'
     *
     * @return string The full hierarchical slug path
     */
    public function getFullSlugAttribute(): string
    {
        $slugs = [$this->slug];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($slugs, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $slugs);
    }

    /**
     * Accessor for the page's full public URL.
     *
     * Generates the complete URL to access this page on the
     * frontend using the hierarchical slug path.
     *
     * @return string The fully qualified URL to the page
     */
    public function getUrlAttribute(): string
    {
        return url('/' . $this->full_slug);
    }

    /**
     * Query scope to filter only published pages.
     *
     * Filters pages with 'published' status and where the
     * published_at date is null or in the past.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Query scope to filter only top-level pages.
     *
     * Filters pages that have no parent (root-level pages).
     * Used for building main navigation menus.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Find a page by its translated slug.
     *
     * Searches for a page with a translation matching the
     * given slug in the specified locale (or current locale).
     *
     * @param string $slug The URL slug to search for
     * @param string|null $locale The locale to search in, defaults to current
     *
     * @return self|null The matching Page or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }

    /**
     * Get the homepage of the site.
     *
     * Retrieves the page marked as homepage that is currently
     * published. Returns null if no homepage is configured.
     *
     * @return self|null The homepage Page or null if not found
     */
    public static function getHomepage(): ?self
    {
        return static::where('is_homepage', true)->published()->first();
    }
}
