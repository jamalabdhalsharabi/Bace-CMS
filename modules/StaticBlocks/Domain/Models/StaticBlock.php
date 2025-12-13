<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

/**
 * StaticBlock Model - Represents reusable content blocks.
 *
 * This model manages static content blocks that can be embedded
 * in pages, templates, or other content areas with multi-language support.
 *
 * @property string $id UUID primary key
 * @property string $identifier Unique block identifier for embedding
 * @property string $type Block type (html, text, widget, etc.)
 * @property bool $is_active Whether block is active
 * @property array|null $settings Block-specific settings as JSON
 * @property string|null $created_by UUID of user who created the block
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StaticBlockTranslation> $translations All translations
 * @property-read StaticBlockTranslation|null $translation Current locale translation
 * @property-read string|null $title Localized title (accessor)
 * @property-read string|null $content Localized content (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|StaticBlock active() Filter active blocks
 * @method static \Illuminate\Database\Eloquent\Builder|StaticBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaticBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaticBlock query()
 */
class StaticBlock extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'static_blocks';

    protected $fillable = ['identifier', 'type', 'is_active', 'settings', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get all translations for this block.
     *
     * @return HasMany<StaticBlockTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(StaticBlockTranslation::class);
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<StaticBlockTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(StaticBlockTranslation::class)->where('locale', app()->getLocale());
    }

    /**
     * Get the localized block title.
     *
     * @return string|null The title
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Get the localized block content.
     *
     * @return string|null The content
     */
    public function getContentAttribute(): ?string
    {
        return $this->translation?->content ?? $this->translations->first()?->content;
    }

    /**
     * Find an active block by identifier with caching.
     *
     * @param string $identifier The block identifier
     * @return self|null The block or null
     */
    public static function findByIdentifier(string $identifier): ?self
    {
        return Cache::remember("static_block_{$identifier}", config('static-blocks.cache.ttl', 3600), fn() =>
            static::where('identifier', $identifier)->where('is_active', true)->with('translation')->first()
        );
    }

    /**
     * Clear the cache for a specific block.
     *
     * @param string $identifier The block identifier
     * @return void
     */
    public static function clearCache(string $identifier): void
    {
        Cache::forget("static_block_{$identifier}");
    }

    /**
     * Scope to filter only active blocks.
     *
     * @param \Illuminate\Database\Eloquent\Builder<StaticBlock> $query
     * @return \Illuminate\Database\Eloquent\Builder<StaticBlock>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
