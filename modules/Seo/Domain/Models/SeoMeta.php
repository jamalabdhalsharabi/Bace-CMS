<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasUuids;

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

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFullTitle(?string $suffix = null): string
    {
        $suffix = $suffix ?? config('seo.defaults.title_suffix');
        $separator = config('seo.defaults.title_separator', ' | ');
        
        return $this->meta_title 
            ? $this->meta_title . $separator . $suffix 
            : $suffix;
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('seoable_type', $type)->where('seoable_id', $id);
    }

    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}
