<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

final class PublishProjectAction extends Action
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    public function execute(Project $project): Project
    {
        $this->repository->update($project->id, [
            'status' => 'published',
            'published_at' => $project->published_at ?? now(),
        ]);

        return $project->fresh();
    }

    public function unpublish(Project $project): Project
    {
        $this->repository->update($project->id, ['status' => 'draft']);

        return $project->fresh();
    }

    public function archive(Project $project): Project
    {
        $this->repository->update($project->id, ['status' => 'archived']);

        return $project->fresh();
    }
}
