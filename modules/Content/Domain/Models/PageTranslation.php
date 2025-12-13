<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PageTranslation
 *
 * Eloquent model representing a page translation
 * for multi-language support.
 *
 * @package Modules\Content\Domain\Models
 *
 * @property string $id
 * @property string $page_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 *
 * @property-read Page $page
 */
class PageTranslation extends Model
{
    use HasUuids;

    protected $table = 'page_translations';

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
