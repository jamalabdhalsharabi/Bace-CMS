<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Repositories\ProjectRepository;

final class DeleteProjectAction extends Action
{
    public function __construct(
        private readonly ProjectRepository $repository
    ) {}

    public function execute(Project $project): bool
    {
        $project->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($project->id);
    }
}
