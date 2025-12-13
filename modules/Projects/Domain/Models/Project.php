<?php

declare(strict_types=1);

namespace Modules\Projects\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasTranslations;
use Modules\Media\Domain\Models\Media;
use Modules\Taxonomy\Traits\HasTaxonomies;

/**
 * Class Project
 *
 * Eloquent model representing a portfolio project with translations,
 * client info, gallery, and case studies.
 *
 * @package Modules\Projects\Domain\Models
 *
 * @property string $id
 * @property string $status
 * @property bool $is_featured
 * @property string|null $client_name
 * @property string|null $client_logo_id
 * @property string|null $client_website
 * @property bool $client_permission
 * @property string|null $project_type
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property array|null $metrics
 * @property \Carbon\Carbon|null $published_at
 * @property int $sort_order
 * @property array|null $meta
 * @property array|null $settings
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ProjectTranslation[] $translations
 * @property-read ProjectTranslation|null $translation
 * @property-read Media|null $clientLogo
 * @property-read string|null $title Localized title (accessor)
 * @property-read string|null $slug Localized slug (accessor)
 * @property-read \App\Models\User|null $author User who created the project
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Project published() Filter published projects
 * @method static \Illuminate\Database\Eloquent\Builder|Project featured() Filter featured projects
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 */
class Project extends Model
{
    use HasUuids;
    use SoftDeletes;
    use HasTaxonomies;
    use HasTranslations;

    /**
     * Translatable attributes (Astrotomic format).
     *
     * @var array<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'description', 'content', 'case_study', 'meta_title', 'meta_description'];

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

    /**
     * Get the client's logo media.
     *
     * @return BelongsTo<Media, Project>
     */
    public function clientLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'client_logo_id');
    }

    /**
     * Get the user who created this project.
     *
     * @return BelongsTo<\App\Models\User, Project>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Scope to filter only published projects.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Project> $query
     * @return \Illuminate\Database\Eloquent\Builder<Project>
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to filter only featured projects.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Project> $query
     * @return \Illuminate\Database\Eloquent\Builder<Project>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Find a project by its localized slug.
     *
     * @param string $slug The slug to search for
     * @param string|null $locale The locale (defaults to current)
     * @return self|null The project or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => $q->where('slug', $slug)->where('locale', $locale))->first();
    }
}
