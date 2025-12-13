<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasTranslations;
use Modules\Media\Domain\Models\Media;

/**
 * Testimonial Model - Represents customer testimonials and reviews.
 *
 * This model manages testimonials with author info, ratings,
 * multi-language content, and featuring capabilities.
 *
 * @property string $id UUID primary key
 * @property string $author_name Name of the testimonial author
 * @property string|null $author_title Author's job title/position
 * @property string|null $author_company Author's company/organization
 * @property string|null $author_avatar_id Foreign key to media for avatar
 * @property int|null $rating Rating value (1-5)
 * @property bool $is_featured Whether testimonial is featured
 * @property bool $is_active Whether testimonial is visible
 * @property int $sort_order Display order
 * @property string|null $source Source of testimonial (google, yelp, direct, etc.)
 * @property string|null $source_url Original URL if from external source
 * @property \Carbon\Carbon|null $date Date of testimonial
 * @property string|null $created_by UUID of user who created
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read Media|null $avatar Author's avatar image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TestimonialTranslation> $translations
 * @property-read TestimonialTranslation|null $translation Current locale translation
 * @property-read string|null $content Localized testimonial content (accessor)
 * @property-read string|null $position Localized author position (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Testimonial active() Filter active testimonials
 * @method static \Illuminate\Database\Eloquent\Builder|Testimonial featured() Filter featured testimonials
 * @method static \Illuminate\Database\Eloquent\Builder|Testimonial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Testimonial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Testimonial query()
 */
class Testimonial extends Model
{
    use HasUuids, SoftDeletes, HasTranslations;

    public array $translatedAttributes = ['content', 'position'];

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

    /**
     * Get the author's avatar image.
     *
     * @return BelongsTo<Media, Testimonial>
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'author_avatar_id');
    }

    /**
     * Scope to filter only active testimonials.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Testimonial> $query
     * @return \Illuminate\Database\Eloquent\Builder<Testimonial>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter only featured testimonials.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Testimonial> $query
     * @return \Illuminate\Database\Eloquent\Builder<Testimonial>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
