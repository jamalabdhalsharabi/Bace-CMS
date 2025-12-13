<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * NotificationTemplate Model - Defines notification templates.
 *
 * This model stores reusable notification templates for emails,
 * SMS, push notifications with multi-language support.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique template identifier
 * @property string $type Template type (welcome, password_reset, etc.)
 * @property array $available_channels Available channels (email, sms, push)
 * @property array|null $variables Available template variables
 * @property bool $is_active Whether template is active
 * @property bool $is_system Whether this is a system template
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NotificationTemplateTranslation> $translations All translations
 * @property-read NotificationTemplateTranslation|null $translation Current locale translation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate active() Filter active templates
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate ofType(string $type) Filter by type
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate query()
 */
class NotificationTemplate extends Model
{
    use HasUuids;

    protected $table = 'notification_templates';

    protected $fillable = [
        'slug',
        'type',
        'available_channels',
        'variables',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'available_channels' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get all translations.
     *
     * @return HasMany<NotificationTemplateTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(NotificationTemplateTranslation::class, 'template_id');
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<NotificationTemplateTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(NotificationTemplateTranslation::class, 'template_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Find a template by its slug.
     *
     * @param string $slug The template slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Scope to filter active templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder<NotificationTemplate> $query
     * @return \Illuminate\Database\Eloquent\Builder<NotificationTemplate>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter templates by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<NotificationTemplate> $query
     * @param string $type The template type
     * @return \Illuminate\Database\Eloquent\Builder<NotificationTemplate>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
