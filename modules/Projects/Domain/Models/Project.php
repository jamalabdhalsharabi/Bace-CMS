<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Media\Domain\Models\Media;
use Modules\Taxonomy\Traits\HasTaxonomies;

class Project extends Model
{
    use HasUuids;
    use SoftDeletes;
    use HasTaxonomies;

    protected $table = 'projects';

    protected $fillable = [
        'status',
        'is_featured',
        'client_name',
        'client_logo_id',
        'client_website',
        'client_permission',
        'project_type',
        'start_date',
        'end_date',
        'metrics',
        'published_at',
        'sort_order',
        'meta',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'client_permission' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'metrics' => 'array',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'meta' => 'array',
        'settings' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ProjectTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function clientLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'client_logo_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
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
        return static::whereHas('translations', fn($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
