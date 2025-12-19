<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Services;

use Modules\Projects\Application\Actions\CreateProjectAction;
use Modules\Projects\Application\Actions\DeleteProjectAction;
use Modules\Projects\Application\Actions\DuplicateProjectAction;
use Modules\Projects\Application\Actions\FeatureProjectAction;
use Modules\Projects\Application\Actions\PublishProjectAction;
use Modules\Projects\Application\Actions\UpdateProjectAction;
use Modules\Projects\Domain\DTO\ProjectData;
use Modules\Projects\Domain\Models\Project;

/**
 * Project Command Service.
 *
 * Orchestrates all write operations for projects via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Projects\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProjectCommandService
{
    /**
     * Create a new ProjectCommandService instance.
     *
     * @param CreateProjectAction $createAction Action for creating projects
     * @param UpdateProjectAction $updateAction Action for updating projects
     * @param DeleteProjectAction $deleteAction Action for deleting projects
     * @param PublishProjectAction $publishAction Action for publishing projects
     * @param DuplicateProjectAction $duplicateAction Action for duplicating projects
     * @param FeatureProjectAction $featureAction Action for featuring projects
     */
    public function __construct(
        private readonly CreateProjectAction $createAction,
        private readonly UpdateProjectAction $updateAction,
        private readonly DeleteProjectAction $deleteAction,
        private readonly PublishProjectAction $publishAction,
        private readonly DuplicateProjectAction $duplicateAction,
        private readonly FeatureProjectAction $featureAction,
    ) {}

    public function create(ProjectData $data): Project
    {
        return $this->createAction->execute($data);
    }

    public function update(Project $project, ProjectData $data): Project
    {
        return $this->updateAction->execute($project, $data);
    }

    public function publish(Project $project): Project
    {
        return $this->publishAction->execute($project);
    }

    public function unpublish(Project $project): Project
    {
        return $this->publishAction->unpublish($project);
    }

    public function archive(Project $project): Project
    {
        return $this->publishAction->archive($project);
    }

    public function delete(Project $project): bool
    {
        return $this->deleteAction->execute($project);
    }

    public function duplicate(Project $project): Project
    {
        return $this->duplicateAction->execute($project);
    }

    public function feature(Project $project): Project
    {
        return $this->featureAction->execute($project);
    }

    public function unfeature(Project $project): Project
    {
        return $this->featureAction->unfeature($project);
    }
}
