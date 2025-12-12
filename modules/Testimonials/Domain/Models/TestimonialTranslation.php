<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestimonialTranslation extends Model
{
    use HasUuids;

    protected $table = 'testimonial_translations';

    protected $fillable = ['testimonial_id', 'locale', 'content', 'excerpt'];

    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }
}
