<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTranslation extends Model
{
    use HasUuids;

    protected $table = 'project_translations';

    protected $fillable = [
        'project_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'description',
        'challenge',
        'solution',
        'results',
        'meta_title',
        'meta_description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
