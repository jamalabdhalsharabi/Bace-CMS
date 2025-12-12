<?php

declare(strict_types=1);

namespace Modules\Content\Services;

use Illuminate\Support\Str;

class SlugGenerator
{
    public function generate(string $title, string $table, string $column = 'slug', ?string $locale = null, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $table, $column, $locale, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, string $table, string $column, ?string $locale, ?string $excludeId): bool
    {
        $query = \DB::table($table)->where($column, $slug);

        if ($locale) {
            $query->where('locale', $locale);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
