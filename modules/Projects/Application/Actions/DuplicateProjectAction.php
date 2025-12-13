<?php

declare(strict_types=1);

namespace Modules\Projects\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Projects\Domain\Models\Project;

final class DuplicateProjectAction extends Action
{
    public function execute(Project $project): Project
    {
        return $this->transaction(function () use ($project) {
            $clone = $project->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($project->translations as $trans) {
                $clone->translations()->create([
                    'locale' => $trans->locale,
                    'title' => $trans->title . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'description' => $trans->description,
                    'content' => $trans->content,
                ]);
            }

            $clone->categories()->sync($project->categories->pluck('id'));

            return $clone->fresh(['translations', 'categories']);
        });
    }
}
