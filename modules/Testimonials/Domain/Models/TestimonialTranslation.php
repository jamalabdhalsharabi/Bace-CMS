<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TestimonialTranslation Model - Stores localized testimonial content.
 *
 * @property string $id UUID primary key
 * @property string $testimonial_id Foreign key to testimonials table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $content Translated testimonial content
 * @property string|null $excerpt Short excerpt of testimonial
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Testimonial $testimonial Parent testimonial
 */
class TestimonialTranslation extends Model
{
    use HasUuids;

    protected $table = 'testimonial_translations';

    protected $fillable = ['testimonial_id', 'locale', 'content', 'excerpt'];

    /**
     * Get the parent testimonial.
     *
     * @return BelongsTo<Testimonial, TestimonialTranslation>
     */
    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }
}
