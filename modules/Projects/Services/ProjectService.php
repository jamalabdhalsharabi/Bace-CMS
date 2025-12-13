<?php

declare(strict_types=1);

namespace Modules\Projects\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Projects\Contracts\ProjectServiceContract;
use Modules\Projects\Domain\Models\Project;

/**
 * Class ProjectService
 *
 * Service class for managing projects including CRUD,
 * workflow, gallery, case studies, and related projects.
 *
 * @package Modules\Projects\Services
 */
class ProjectService implements ProjectServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Project::with(['translation', 'clientLogo']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }
        if (!empty($filters['type'])) {
            $query->where('project_type', $filters['type']);
        }

        return $query->orderBy('sort_order')->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Project
    {
        return Project::with(['translations', 'clientLogo'])->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Project
    {
        return Project::findBySlug($slug)?->load(['translations', 'clientLogo']);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? false,
                'client_name' => $data['client_name'] ?? null,
                'client_logo_id' => $data['client_logo_id'] ?? null,
                'client_website' => $data['client_website'] ?? null,
                'client_permission' => $data['client_permission'] ?? false,
                'project_type' => $data['project_type'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'metrics' => $data['metrics'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $project->translations()->create([
                        'locale' => $locale,
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'excerpt' => $trans['excerpt'] ?? null,
                        'description' => $trans['description'] ?? null,
                        'challenge' => $trans['challenge'] ?? null,
                        'solution' => $trans['solution'] ?? null,
                        'results' => $trans['results'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]);
                }
            }

            return $project->fresh(['translations']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update(array_filter($data, fn($v) => $v !== null));

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $project->translations()->updateOrCreate(
                        ['locale' => $locale],
                        array_filter($trans, fn($v) => $v !== null)
                    );
                }
            }

            return $project->fresh(['translations']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Project $project): Project
    {
        $project->update(['status' => 'published', 'published_at' => now()]);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDraft(Project $project, array $data): Project
    {
        $data['status'] = 'draft';
        return $this->update($project, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForReview(Project $project): Project
    {
        $project->update(['status' => 'pending_review']);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function approve(Project $project): Project
    {
        $project->update(['status' => 'approved']);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Project $project, ?string $reason = null): Project
    {
        $project->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(Project $project, \DateTime $date): Project
    {
        $project->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(Project $project): Project
    {
        $project->update(['status' => 'draft']);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function archive(Project $project): Project
    {
        $project->update(['status' => 'archived', 'archived_at' => now()]);
        return $project->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function addGalleryImages(Project $project, array $mediaIds): Project
    {
        $project->gallery()->syncWithoutDetaching($mediaIds);
        return $project->fresh();
    }

    public function removeGalleryImage(Project $project, string $mediaId): Project
    {
        $project->gallery()->detach($mediaId);
        return $project->fresh();
    }

    public function reorderGallery(Project $project, array $order): Project
    {
        foreach ($order as $index => $mediaId) {
            $project->gallery()->updateExistingPivot($mediaId, ['order' => $index]);
        }
        return $project->fresh();
    }

    public function setFeaturedImage(Project $project, string $mediaId): Project
    {
        $project->update(['featured_image_id' => $mediaId]);
        return $project->fresh();
    }

    public function addCaseStudy(Project $project, array $data): mixed
    {
        return $project->update(['case_study' => $data]);
    }

    public function updateCaseStudy(Project $project, array $data): mixed
    {
        return $project->update(['case_study' => $data]);
    }

    public function addBeforeAfter(Project $project, array $data): mixed
    {
        $beforeAfter = $project->before_after ?? [];
        $beforeAfter[] = $data;
        $project->update(['before_after' => $beforeAfter]);
        return $data;
    }

    public function removeBeforeAfter(Project $project, string $id): bool
    {
        $beforeAfter = collect($project->before_after ?? [])->reject(fn($item) => ($item['id'] ?? null) === $id)->values()->all();
        $project->update(['before_after' => $beforeAfter]);
        return true;
    }

    public function feature(Project $project): Project
    {
        $project->update(['is_featured' => true]);
        return $project->fresh();
    }

    public function unfeature(Project $project): Project
    {
        $project->update(['is_featured' => false]);
        return $project->fresh();
    }

    public function duplicate(Project $project): Project
    {
        return DB::transaction(function () use ($project) {
            $clone = $project->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->save();
            foreach ($project->translations as $trans) {
                $clone->translations()->create($trans->toArray());
            }
            return $clone->fresh(['translations']);
        });
    }

    public function syncCategories(Project $project, array $categoryIds): Project
    {
        $project->categories()->sync($categoryIds);
        return $project->fresh();
    }

    public function attachRelated(Project $project, array $projectIds): Project
    {
        $project->relatedProjects()->sync($projectIds);
        return $project->fresh();
    }
}
