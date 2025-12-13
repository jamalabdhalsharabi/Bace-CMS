<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ArticleTranslation
 *
 * Eloquent model representing an article translation
 * for multi-language support.
 *
 * @package Modules\Content\Domain\Models
 *
 * @property string $id
 * @property string $article_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 *
 * @property-read Article $article
 */
class ArticleTranslation extends Model
{
    use HasUuids;

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
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
