<?php

declare(strict_types=1);

namespace Modules\Seo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Seo\Domain\Models\SeoMeta;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable')
            ->where('locale', app()->getLocale());
    }

    public function seoMetas()
    {
        return $this->morphMany(SeoMeta::class, 'seoable');
    }

    public function getMetaTitle(): ?string
    {
        return $this->seoMeta?->meta_title ?? $this->title ?? $this->name ?? null;
    }

    public function getMetaDescription(): ?string
    {
        return $this->seoMeta?->meta_description ?? $this->excerpt ?? null;
    }

    public function setSeoMeta(array $data, ?string $locale = null): SeoMeta
    {
        $locale = $locale ?? app()->getLocale();
        
        return $this->seoMetas()->updateOrCreate(
            ['locale' => $locale],
            $data
        );
    }
}
