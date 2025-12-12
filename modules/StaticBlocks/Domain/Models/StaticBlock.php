<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class StaticBlock extends Model
{
    use HasUuids;

    protected $table = 'static_blocks';

    protected $fillable = ['identifier', 'type', 'is_active', 'settings', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(StaticBlockTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(StaticBlockTranslation::class)->where('locale', app()->getLocale());
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    public function getContentAttribute(): ?string
    {
        return $this->translation?->content ?? $this->translations->first()?->content;
    }

    public static function findByIdentifier(string $identifier): ?self
    {
        return Cache::remember("static_block_{$identifier}", config('static-blocks.cache.ttl', 3600), fn() =>
            static::where('identifier', $identifier)->where('is_active', true)->with('translation')->first()
        );
    }

    public static function clearCache(string $identifier): void
    {
        Cache::forget("static_block_{$identifier}");
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
