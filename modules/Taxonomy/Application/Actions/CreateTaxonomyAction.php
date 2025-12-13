<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\DTO\TaxonomyData;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

final class CreateTaxonomyAction extends Action
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function execute(TaxonomyData $data): Taxonomy
    {
        return $this->transaction(function () use ($data) {
            $taxonomy = $this->repository->create([
                'type_id' => $data->type_id,
                'parent_id' => $data->parent_id,
                'sort_order' => $data->sort_order,
                'meta' => $data->meta,
            ]);

            foreach ($data->translations as $locale => $trans) {
                $taxonomy->translations()->create([
                    'locale' => $locale,
                    'name' => $trans['name'],
                    'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                    'description' => $trans['description'] ?? null,
                ]);
            }

            return $taxonomy->fresh(['translations']);
        });
    }
}
