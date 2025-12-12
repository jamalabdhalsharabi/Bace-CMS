<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
