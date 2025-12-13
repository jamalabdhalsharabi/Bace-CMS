<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Page Model - Represents static pages with hierarchical structure.
 *
 * This model handles CMS pages with parent-child relationships,
 * multi-language support, custom templates, and page builder sections.
 *
 * @property string $id UUID primary key
 * @property string $status Publication status (draft, pending, published, archived)
 * @property bool $is_homepage Whether this is the site homepage
 * @property bool $is_system Whether this is a protected system page
 * @property string $template Page template to use for rendering
 * @property string|null $parent_id Foreign key to parent page
 * @property int $depth Nesting depth in page hierarchy (0 = root)
 * @property string|null $path Full path from root (e.g., 'about/team')
 * @property int $sort_order Display order among siblings
 * @property int $version Content version number
 * @property \Carbon\Carbon|null $published_at Publication date/time
 * @property \Carbon\Carbon|null $scheduled_at Scheduled publication date
 * @property string $created_by UUID of user who created the page
 * @property string|null $updated_by UUID of user who last updated
 * @property string|null $deleted_by UUID of user who deleted
 * @property array|null $sections Page builder sections as JSON
 * @property array|null $meta Additional metadata as JSON
 * @property array|null $settings Page-specific settings as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read Page|null $parent Parent page
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Page> $children Child pages
 * @property-read \App\Models\User $creator User who created the page
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PageTranslation> $translations All translations
 * @property-read PageTranslation|null $translation Current locale translation
 * @property-read string|null $title Localized title (accessor)
 * @property-read string|null $slug Localized slug (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Page published() Filter published pages
 * @method static \Illuminate\Database\Eloquent\Builder|Page root() Filter root-level pages
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 */
class Page extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'pages';

    protected $fillable = [
        'status',
        'is_homepage',
        'is_system',
        'template',
        'parent_id',
        'depth',
        'path',
        'sort_order',
        'version',
        'published_at',
        'scheduled_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'sections',
        'meta',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'is_system' => 'boolean',
            'depth' => 'integer',
            'sort_order' => 'integer',
            'version' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'sections' => 'array',
            'meta' => 'array',
            'settings' => 'array',
        ];
    }

    /**
     * Get the parent page.
     *
     * @return BelongsTo<Page, Page>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child pages ordered by sort_order.
     *
     * @return HasMany<Page>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the user who created this page.
     *
     * @return BelongsTo<\App\Models\User, Page>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get all translations for this page.
     *
     * @return HasMany<PageTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<PageTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class)->where('locale', app()->getLocale());
    }

    /**
     * Get the localized title.
     *
     * @return string|null The page title
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Get the localized slug.
     *
     * @return string|null The page slug
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Scope to filter only published pages.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Page> $query
     * @return \Illuminate\Database\Eloquent\Builder<Page>
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Scope to filter only root-level pages (no parent).
     *
     * @param \Illuminate\Database\Eloquent\Builder<Page> $query
     * @return \Illuminate\Database\Eloquent\Builder<Page>
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Find a page by its localized slug.
     *
     * @param string $slug The slug to search for
     * @param string|null $locale The locale (defaults to current)
     * @return self|null The page or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        return static::whereHas('translations', fn ($q) => $q->where('slug', $slug)->where('locale', $locale))->first();
    }

    /**
     * Get the site homepage.
     *
     * @return self|null The homepage or null if not set
     */
    public static function getHomepage(): ?self
    {
        return static::where('is_homepage', true)->published()->first();
    }
}
