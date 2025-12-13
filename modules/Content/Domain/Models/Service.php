<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Service Model - Represents services offered by the organization.
 *
 * This model handles service listings with hierarchical structure,
 * multi-language support, featuring, and approval workflow.
 *
 * @property string $id UUID primary key
 * @property string $status Publication status (draft, pending, published, archived)
 * @property bool $is_featured Whether service is featured/highlighted
 * @property \Carbon\Carbon|null $published_at Publication date/time
 * @property \Carbon\Carbon|null $scheduled_at Scheduled publication date
 * @property string|null $parent_id Foreign key to parent service
 * @property int $sort_order Display order among siblings
 * @property string $slug URL-friendly identifier
 * @property int $version Content version number
 * @property string $created_by UUID of user who created the service
 * @property string|null $updated_by UUID of user who last updated
 * @property string|null $deleted_by UUID of user who deleted
 * @property string|null $approved_by UUID of user who approved
 * @property \Carbon\Carbon|null $approved_at Approval timestamp
 * @property array|null $meta Additional metadata as JSON
 * @property array|null $settings Service-specific settings as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read Service|null $parent Parent service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Service> $children Child services
 * @property-read \App\Models\User $creator User who created the service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ServiceTranslation> $translations All translations
 * @property-read ServiceTranslation|null $translation Current locale translation
 * @property-read string|null $title Localized title (accessor)
 * @property-read string|null $slug Localized slug (accessor)
 * @property-read string|null $description Localized description (accessor)
 * @property-read string|null $content Localized content (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Service published() Filter published services
 * @method static \Illuminate\Database\Eloquent\Builder|Service featured() Filter featured services
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 */
class Service extends Model
{
    use HasUuids;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    protected $fillable = [
        'status',
        'is_featured',
        'published_at',
        'scheduled_at',
        'parent_id',
        'sort_order',
        'slug',
        'version',
        'created_by',
        'updated_by',
        'deleted_by',
        'approved_by',
        'approved_at',
        'meta',
        'settings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'version' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'approved_at' => 'datetime',
            'meta' => 'array',
            'settings' => 'array',
        ];
    }

    /**
     * Get the parent service.
     *
     * @return BelongsTo<Service, Service>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child services ordered by sort_order.
     *
     * @return HasMany<Service>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the user who created this service.
     *
     * @return BelongsTo<\App\Models\User, Service>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get all translations for this service.
     *
     * @return HasMany<ServiceTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<ServiceTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ServiceTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * Get the localized title.
     *
     * @return string|null The service title
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Get the localized slug.
     *
     * @return string|null The service slug
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Get the localized description.
     *
     * @return string|null The service description
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    /**
     * Get the localized content.
     *
     * @return string|null The service content
     */
    public function getContentAttribute(): ?string
    {
        return $this->translation?->content;
    }

    /**
     * Scope to filter only published services.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Service> $query
     * @return \Illuminate\Database\Eloquent\Builder<Service>
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to filter only featured services.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Service> $query
     * @return \Illuminate\Database\Eloquent\Builder<Service>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Find a service by its localized slug.
     *
     * @param string $slug The slug to search for
     * @param string|null $locale The locale (defaults to current)
     * @return self|null The service or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        return static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
