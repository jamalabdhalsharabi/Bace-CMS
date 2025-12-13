<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProjectTranslation
 *
 * Eloquent model representing a project translation
 * for multi-language support including case study content.
 *
 * @package Modules\Projects\Domain\Models
 *
 * @property string $id
 * @property string $project_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $description
 * @property string|null $challenge
 * @property string|null $solution
 * @property string|null $results
 * @property string|null $meta_title
 * @property string|null $meta_description
 *
 * @property-read Project $project
 */
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

    /**
     * Get the parent project.
     *
     * @return BelongsTo<Project, ProjectTranslation>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
