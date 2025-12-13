<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MediaTranslation Model - Stores localized media metadata.
 *
 * This model holds translated titles, alt text, captions, and
 * descriptions for media files in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $media_id Foreign key to media table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string|null $title Translated media title
 * @property string|null $alt_text Translated alt text for accessibility
 * @property string|null $caption Translated caption
 * @property string|null $description Translated description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Media $media The parent media file
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTranslation query()
 */
class MediaTranslation extends Model
{
    use HasUuids;

    protected $table = 'media_translations';

    protected $fillable = [
        'media_id',
        'locale',
        'title',
        'alt_text',
        'caption',
        'description',
    ];

    /**
     * Get the media file that owns this translation.
     *
     * @return BelongsTo<Media, MediaTranslation>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
