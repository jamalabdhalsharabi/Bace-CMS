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
        return $this->hasMany(ArticleTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ArticleTranslation::class)
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

    public function getExcerptAttribute(): ?string
    {
        return $this->translation?->excerpt;
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

    public function getUrlAttribute(): string
    {
        return url('/articles/' . $this->slug);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByAuthor($query, string $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
