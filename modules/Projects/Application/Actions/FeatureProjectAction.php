<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

final class FeatureProjectAction extends Action
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    public function execute(Project $project): Project
    {
        $this->repository->update($project->id, ['is_featured' => true]);

        return $project->fresh();
    }

    public function unfeature(Project $project): Project
    {
        $this->repository->update($project->id, ['is_featured' => false]);

        return $project->fresh();
    }
}
