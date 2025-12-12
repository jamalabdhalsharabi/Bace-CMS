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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

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

    public function getUrlAttribute(): string
    {
        return url('/' . $this->full_slug);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }

    public static function getHomepage(): ?self
    {
        return static::where('is_homepage', true)->published()->first();
    }
}
