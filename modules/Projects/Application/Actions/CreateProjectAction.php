<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Projects\Domain\DTO\ProjectData;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

final class CreateProjectAction extends Action
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    public function execute(ProjectData $data): Project
    {
        return $this->transaction(function () use ($data) {
            $project = $this->repository->create([
                'status' => $data->status,
                'is_featured' => $data->is_featured,
                'client_name' => $data->client_name,
                'project_date' => $data->project_date,
                'project_url' => $data->project_url,
                'featured_image_id' => $data->featured_image_id,
                'meta' => $data->meta,
                'created_by' => $this->userId(),
            ]);

            foreach ($data->translations as $locale => $trans) {
                $project->translations()->create([
                    'locale' => $locale,
                    'title' => $trans['title'],
                    'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                    'description' => $trans['description'] ?? null,
                    'content' => $trans['content'] ?? null,
                    'meta_title' => $trans['meta_title'] ?? null,
                    'meta_description' => $trans['meta_description'] ?? null,
                ]);
            }

            if ($data->category_ids) {
                $project->categories()->sync($data->category_ids);
            }

            if ($data->gallery_ids) {
                $project->gallery()->sync($data->gallery_ids);
            }

            return $project->fresh(['translations', 'categories', 'gallery']);
        });
    }
}
