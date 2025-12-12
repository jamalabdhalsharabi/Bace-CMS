<?php

declare(strict_types=1);

namespace Modules\Projects\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Projects\Domain\Models\Project;

interface ProjectServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator;
    public function find(string $id): ?Project;
    public function findBySlug(string $slug): ?Project;
    public function create(array $data): Project;
    public function update(Project $project, array $data): Project;
    public function delete(Project $project): bool;

    // Workflow
    public function saveDraft(Project $project, array $data): Project;
    public function submitForReview(Project $project): Project;
    public function approve(Project $project): Project;
    public function reject(Project $project, ?string $reason = null): Project;
    public function publish(Project $project): Project;
    public function schedule(Project $project, \DateTime $date): Project;
    public function unpublish(Project $project): Project;
    public function archive(Project $project): Project;

    // Gallery
    public function addGalleryImages(Project $project, array $mediaIds): Project;
    public function removeGalleryImage(Project $project, string $mediaId): Project;
    public function reorderGallery(Project $project, array $order): Project;
    public function setFeaturedImage(Project $project, string $mediaId): Project;

    // Case Study
    public function addCaseStudy(Project $project, array $data): mixed;
    public function updateCaseStudy(Project $project, array $data): mixed;

    // Before/After
    public function addBeforeAfter(Project $project, array $data): mixed;
    public function removeBeforeAfter(Project $project, string $id): bool;

    // Other
    public function feature(Project $project): Project;
    public function unfeature(Project $project): Project;
    public function duplicate(Project $project): Project;
    public function syncCategories(Project $project, array $categoryIds): Project;
    public function attachRelated(Project $project, array $projectIds): Project;
}
