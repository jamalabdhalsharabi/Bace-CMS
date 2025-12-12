<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Media\Domain\Models\Media;

class Testimonial extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'testimonials';

    protected $fillable = [
        'author_name', 'author_title', 'author_company', 'author_avatar_id',
        'rating', 'is_featured', 'is_active', 'sort_order', 'source',
        'source_url', 'date', 'created_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'date' => 'date',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TestimonialTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(TestimonialTranslation::class)->where('locale', app()->getLocale());
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'author_avatar_id');
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content ?? $this->translations->first()?->content;
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
}
