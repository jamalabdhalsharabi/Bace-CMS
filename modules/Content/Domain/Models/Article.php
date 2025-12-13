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
 * Article Model - Represents blog posts, news, and other article content.
 *
 * This model handles various types of articles with multi-language support,
 * featuring, pinning, comments, and comprehensive SEO capabilities.
 *
 * @property string $id UUID primary key
 * @property string $author_id Foreign key to the article author
 * @property string|null $featured_image_id Foreign key to featured image in media table
 * @property string $type Article type (e.g., 'post', 'news', 'blog')
 * @property string $status Publication status (draft, pending, published, archived)
 * @property bool $is_featured Whether article is featured/highlighted
 * @property bool $is_pinned Whether article is pinned to top
 * @property string|null $pin_position Pin position (top, category, etc.)
 * @property \Carbon\Carbon|null $pin_expires_at When pin status expires
 * @property bool $allow_comments Whether comments are allowed
 * @property \Carbon\Carbon|null $comments_closed_at When comments were closed
 * @property int $view_count Total view count
 * @property int $comment_count Cached comment count
 * @property int $reading_time Estimated reading time in minutes
 * @property int|null $word_count Total word count
 * @property int $version Content version number
 * @property \Carbon\Carbon|null $published_at Publication date/time
 * @property \Carbon\Carbon|null $scheduled_at Scheduled publication date
 * @property string $created_by UUID of user who created the article
 * @property string|null $updated_by UUID of user who last updated
 * @property string|null $deleted_by UUID of user who deleted
 * @property array|null $meta Additional metadata as JSON
 * @property array|null $settings Article-specific settings as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read \App\Models\User $author Article author
 * @property-read \App\Models\User $creator User who created the record
 * @property-read \Modules\Media\Domain\Models\Media|null $featuredImage Featured image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ArticleTranslation> $translations All translations
 * @property-read ArticleTranslation|null $translation Current locale translation
 * @property-read string|null $title Localized title (accessor)
 * @property-read string|null $slug Localized slug (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Article published() Filter published articles
 * @method static \Illuminate\Database\Eloquent\Builder|Article featured() Filter featured articles
 * @method static \Illuminate\Database\Eloquent\Builder|Article ofType(string $type) Filter by article type
 * @method static \Illuminate\Database\Eloquent\Builder|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Article query()
 */
class Article extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'articles';

    protected $fillable = [
        'author_id',
        'featured_image_id',
        'type',
        'status',
        'is_featured',
        'is_pinned',
        'pin_position',
        'pin_expires_at',
        'allow_comments',
        'comments_closed_at',
        'view_count',
        'comment_count',
        'reading_time',
        'word_count',
        'version',
        'published_at',
        'scheduled_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'meta',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'view_count' => 'integer',
            'comment_count' => 'integer',
            'reading_time' => 'integer',
            'word_count' => 'integer',
            'version' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'pin_expires_at' => 'datetime',
            'comments_closed_at' => 'datetime',
            'meta' => 'array',
            'settings' => 'array',
        ];
    }

    /**
     * Get the article author.
     *
     * @return BelongsTo<\App\Models\User, Article>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    /**
     * Get the user who created the article.
     *
     * @return BelongsTo<\App\Models\User, Article>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get the article's featured image.
     *
     * @return BelongsTo<\Modules\Media\Domain\Models\Media, Article>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    /**
     * Get all translations for this article.
     *
     * @return HasMany<ArticleTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<ArticleTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ArticleTranslation::class)->where('locale', app()->getLocale());
    }

    /**
     * Get the localized title.
     *
     * Returns current locale title, falling back to first available translation.
     *
     * @return string|null The article title
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Get the localized slug.
     *
     * Returns current locale slug, falling back to first available translation.
     *
     * @return string|null The article slug
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Scope to filter only published articles.
     *
     * Filters articles with 'published' status and publication date in the past.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Article> $query
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Scope to filter only featured articles.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Article> $query
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter articles by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Article> $query
     * @param string $type The article type to filter by
     * @return \Illuminate\Database\Eloquent\Builder<Article>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Find an article by its localized slug.
     *
     * @param string $slug The slug to search for
     * @param string|null $locale The locale to search in (defaults to current)
     * @return self|null The article or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        return static::whereHas('translations', fn ($q) => $q->where('slug', $slug)->where('locale', $locale))->first();
    }
}
