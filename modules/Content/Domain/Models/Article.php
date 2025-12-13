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
use Modules\Core\Traits\HasRevisions;
use Modules\Core\Traits\HasStatus;

/**
 * Class Article
 *
 * Eloquent model representing a blog article or news post
 * with translations, revisions, and publishing capabilities.
 *
 * @package Modules\Content\Domain\Models
 *
 * @property string $id
 * @property string|null $author_id
 * @property string|null $featured_image_id
 * @property string|null $type
 * @property string $status
 * @property bool $is_featured
 * @property bool $is_commentable
 * @property int $view_count
 * @property int|null $reading_time
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ArticleTranslation[] $translations
 * @property-read ArticleTranslation|null $translation
 * @property-read string|null $title
 * @property-read string|null $slug
 * @property-read string|null $excerpt
 * @property-read string|null $content
 * @property-read string $url
 */
class Article extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use HasStatus;
    use HasMedia;
    use HasRevisions;

    protected $table = 'articles';

    protected $fillable = [
        'author_id',
        'featured_image_id',
        'type',
        'status',
        'is_featured',
        'is_commentable',
        'view_count',
        'reading_time',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_commentable' => 'boolean',
        'view_count' => 'integer',
        'reading_time' => 'integer',
        'published_at' => 'datetime',
    ];

    protected array $translatable = ['title', 'slug', 'excerpt', 'content'];

    /**
     * Define the belongs-to relationship with the article's author.
     *
     * Retrieves the User model who created this article. The author
     * is responsible for the content and can edit the article.
     *
     * @return BelongsTo The belongs-to relationship instance to the User model
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    /**
     * Define the belongs-to relationship with the featured image.
     *
     * Retrieves the Media model representing the article's main
     * featured image used for thumbnails and social sharing.
     *
     * @return BelongsTo The belongs-to relationship instance to Media model
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    /**
     * Define the has-many relationship with article translations.
     *
     * Retrieves all translation records for this article across
     * all supported locales including title, content, and SEO fields.
     *
     * @return HasMany The has-many relationship instance to ArticleTranslation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Define the has-one relationship with the current locale translation.
     *
     * Retrieves the translation record matching the application's
     * current locale setting for displaying localized content.
     *
     * @return HasOne The has-one relationship instance to ArticleTranslation
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ArticleTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * Accessor for the article's localized title.
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
     * Accessor for the article's localized URL slug.
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
     * Accessor for the article's localized excerpt.
     *
     * Returns the short summary/excerpt from the current locale
     * translation for use in article listings and previews.
     *
     * @return string|null The localized excerpt or null if not set
     */
    public function getExcerptAttribute(): ?string
    {
        return $this->translation?->excerpt;
    }

    /**
     * Accessor for the article's localized main content.
     *
     * Returns the full HTML content from the current locale
     * translation for displaying the article body.
     *
     * @return string|null The localized content or null if not set
     */
    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

    /**
     * Accessor for the article's full public URL.
     *
     * Generates the complete URL path to view this article
     * on the frontend using the localized slug.
     *
     * @return string The fully qualified URL to the article
     */
    public function getUrlAttribute(): string
    {
        return url('/articles/' . $this->slug);
    }

    /**
     * Increment the article's view counter.
     *
     * Atomically increases the view_count field by one.
     * Used for tracking article popularity and analytics.
     *
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Calculate the estimated reading time for the article.
     *
     * Counts words in the content (excluding HTML tags) and
     * divides by average reading speed of 200 words per minute.
     * Returns a minimum of 1 minute.
     *
     * @return int Estimated reading time in minutes
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Query scope to filter only published articles.
     *
     * Filters articles with 'published' status and where the
     * published_at date is null or in the past. Excludes
     * scheduled future articles.
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
     * Query scope to filter only featured articles.
     *
     * Filters articles where is_featured flag is true.
     * Featured articles are highlighted on the homepage or listings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Query scope to filter articles by content type.
     *
     * Filters articles matching the specified type such as
     * 'blog', 'news', 'tutorial', etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $type The article type to filter by
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Query scope to filter articles by author.
     *
     * Filters articles created by the specified author user ID.
     * Useful for author profile pages and filtering.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $authorId The UUID of the author to filter by
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeByAuthor($query, string $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Find an article by its translated slug.
     *
     * Searches for an article with a translation matching the
     * given slug in the specified locale (or current locale).
     * Returns null if no matching article is found.
     *
     * @param string $slug The URL slug to search for
     * @param string|null $locale The locale to search in, defaults to current locale
     *
     * @return self|null The matching Article or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
