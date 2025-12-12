<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTranslation extends Model
{
    use HasUuids;

    protected $table = 'taxonomy_translations';

    protected $fillable = [
        'taxonomy_id',
        'locale',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
