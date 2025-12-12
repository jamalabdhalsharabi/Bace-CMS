<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Taxonomy\Domain\Models\TaxonomyType;

class TaxonomyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = config('taxonomy.default_types', []);

        foreach ($types as $type) {
            TaxonomyType::firstOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => json_encode($type['name']),
                    'is_hierarchical' => $type['is_hierarchical'] ?? false,
                    'is_multiple' => $type['is_multiple'] ?? true,
                    'applies_to' => $type['applies_to'] ?? [],
                ]
            );
        }
    }
}
