<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\HasOrdering;

class MenuItem extends Model
{
    use HasUuids;
    use HasOrdering;

    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'linkable_id',
        'linkable_type',
        'title',
        'url',
        'target',
        'icon',
        'css_class',
        'ordering',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'title' => 'array',
        'ordering' => 'integer',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTitleAttribute($value): string
    {
        $titles = json_decode($value, true) ?? [];
        return $titles[app()->getLocale()] ?? $titles['en'] ?? '';
    }

    public function getLocalizedTitles(): array
    {
        return json_decode($this->attributes['title'] ?? '{}', true) ?? [];
    }

    public function getUrlAttribute($value): string
    {
        if ($this->type === 'custom') {
            return $value ?? '#';
        }

        if ($this->linkable) {
            return $this->linkable->url ?? '#';
        }

        return $value ?? '#';
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }
}
