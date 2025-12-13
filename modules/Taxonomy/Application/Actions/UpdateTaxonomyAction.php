<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\DTO\TaxonomyData;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

final class UpdateTaxonomyAction extends Action
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function execute(Taxonomy $taxonomy, TaxonomyData $data): Taxonomy
    {
        return $this->transaction(function () use ($taxonomy, $data) {
            $this->repository->update($taxonomy->id, [
                'parent_id' => $data->parent_id ?? $taxonomy->parent_id,
                'sort_order' => $data->sort_order,
                'meta' => $data->meta ?? $taxonomy->meta,
            ]);

            foreach ($data->translations as $locale => $trans) {
                $taxonomy->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $trans['name'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                        'description' => $trans['description'] ?? null,
                    ]
                );
            }

            return $taxonomy->fresh(['translations']);
        });
    }
}
