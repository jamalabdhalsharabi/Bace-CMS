<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ArticleTranslation Model - Stores localized content for articles.
 *
 * This model holds translated content for articles including title, slug,
 * content body, and SEO metadata for each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $article_id Foreign key to articles table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $title Translated article title
 * @property string $slug URL-friendly slug for this locale
 * @property string|null $excerpt Short summary or teaser
 * @property string|null $content Full article content (HTML)
 * @property string|null $meta_title SEO meta title
 * @property string|null $meta_description SEO meta description
 * @property string|null $meta_keywords SEO keywords (comma-separated)
 * @property string|null $social_title Social media share title
 * @property string|null $social_description Social media share description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Article $article The parent article
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTranslation query()
 */
class ArticleTranslation extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_translations';

    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'social_title',
        'social_description',
    ];

    /**
     * Get the article that owns this translation.
     *
     * @return BelongsTo<Article, ArticleTranslation>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
