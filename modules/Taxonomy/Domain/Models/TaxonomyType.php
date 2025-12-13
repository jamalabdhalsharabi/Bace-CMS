<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TaxonomyType
 *
 * Eloquent model representing a taxonomy type definition
 * (e.g., categories, tags) with configuration.
 *
 * @package Modules\Taxonomy\Domain\Models
 *
 * @property string $id
 * @property string $slug
 * @property array $name
 * @property bool $is_hierarchical
 * @property bool $is_multiple
 * @property array|null $applies_to
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Taxonomy[] $taxonomies
 */
class TaxonomyType extends Model
{
    use HasUuids;

    protected $table = 'taxonomy_types';

    protected $fillable = [
        'slug',
        'name',
        'is_hierarchical',
        'is_multiple',
        'applies_to',
    ];

    protected $casts = [
        'name' => 'array',
        'is_hierarchical' => 'boolean',
        'is_multiple' => 'boolean',
        'applies_to' => 'array',
    ];

    public function taxonomies(): HasMany
    {
        return $this->hasMany(Taxonomy::class, 'type_id');
    }

    public function getNameAttribute($value): string
    {
        $names = json_decode($value, true) ?? [];
        return $names[app()->getLocale()] ?? $names['en'] ?? $this->slug;
    }

    public function getLocalizedNames(): array
    {
        return json_decode($this->attributes['name'], true) ?? [];
    }

    public function appliesToModel(string $model): bool
    {
        return in_array($model, $this->applies_to ?? [], true);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getForModel(string $model): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereJsonContains('applies_to', $model)->get();
    }
}
