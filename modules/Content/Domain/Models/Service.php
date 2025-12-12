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

class Service extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use HasStatus;
    use HasMedia;
    use HasOrdering;

    protected $table = 'services';

    protected $fillable = [
        'featured_image_id',
        'icon',
        'status',
        'is_featured',
        'ordering',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'ordering' => 'integer',
        'published_at' => 'datetime',
    ];

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ServiceTranslation::class)
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

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
