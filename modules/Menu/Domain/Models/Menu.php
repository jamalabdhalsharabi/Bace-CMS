<?php

declare(strict_types=1);

namespace Modules\Menu\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasUuids;

    protected $table = 'menus';

    protected $fillable = [
        'slug',
        'name',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->ordered();
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->items()->with('children.children')->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function findByLocation(string $location): ?self
    {
        return static::where('location', $location)->active()->first();
    }
}
