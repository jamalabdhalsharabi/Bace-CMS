<?php

declare(strict_types=1);

namespace Modules\Services\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Services\Domain\DTO\ServiceData;
use Modules\Services\Domain\Models\Service;
use Modules\Services\Domain\Repositories\ServiceRepository;

final class UpdateServiceAction extends Action
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function execute(Service $service, ServiceData $data): Service
    {
        return $this->transaction(function () use ($service, $data) {
            $this->repository->update($service->id, [
                'is_featured' => $data->is_featured,
                'featured_image_id' => $data->featured_image_id ?? $service->featured_image_id,
                'icon' => $data->icon ?? $service->icon,
                'sort_order' => $data->sort_order,
                'meta' => $data->meta ?? $service->meta,
                'updated_by' => $this->userId(),
            ]);

            foreach ($data->translations as $locale => $trans) {
                $service->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'description' => $trans['description'] ?? null,
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]
                );
            }

            if ($data->category_ids !== null) {
                $service->categories()->sync($data->category_ids);
            }

            return $service->fresh(['translations', 'categories']);
        });
    }
}
