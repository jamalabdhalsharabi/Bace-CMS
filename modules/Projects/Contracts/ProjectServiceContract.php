<?php

declare(strict_types=1);

namespace Modules\Projects\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Projects\Domain\Models\Project;

/**
 * Interface ProjectServiceContract
 * 
 * Defines the contract for project/portfolio management services.
 * Handles CRUD, workflow, gallery, case studies, before/after comparisons,
 * featuring, cloning, and category/relation management.
 * 
 * @package Modules\Projects\Contracts
 */
interface ProjectServiceContract
{
    /** @param array $filters @param int $perPage @return LengthAwarePaginator */
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    /** @param string $id @return Project|null */
    public function find(string $id): ?Project;

    /** @param string $slug @return Project|null */
    public function findBySlug(string $slug): ?Project;

    /** @param array $data @return Project */
    public function create(array $data): Project;

    /** @param Project $project @param array $data @return Project */
    public function update(Project $project, array $data): Project;

    /** @param Project $project @return bool */
    public function delete(Project $project): bool;

    /** @param Project $project @param array $data @return Project */
    public function saveDraft(Project $project, array $data): Project;

    /** @param Project $project @return Project */
    public function submitForReview(Project $project): Project;

    /** @param Project $project @return Project */
    public function approve(Project $project): Project;

    /** @param Project $project @param string|null $reason @return Project */
    public function reject(Project $project, ?string $reason = null): Project;

    /** @param Project $project @return Project */
    public function publish(Project $project): Project;

    /** @param Project $project @param \DateTime $date @return Project */
    public function schedule(Project $project, \DateTime $date): Project;

    /** @param Project $project @return Project */
    public function unpublish(Project $project): Project;

    /** @param Project $project @return Project */
    public function archive(Project $project): Project;

    /** @param Project $project @param array $mediaIds @return Project */
    public function addGalleryImages(Project $project, array $mediaIds): Project;

    /** @param Project $project @param string $mediaId @return Project */
    public function removeGalleryImage(Project $project, string $mediaId): Project;

    /** @param Project $project @param array $order @return Project */
    public function reorderGallery(Project $project, array $order): Project;

    /** @param Project $project @param string $mediaId @return Project */
    public function setFeaturedImage(Project $project, string $mediaId): Project;

    /** @param Project $project @param array $data @return mixed */
    public function addCaseStudy(Project $project, array $data): mixed;

    /** @param Project $project @param array $data @return mixed */
    public function updateCaseStudy(Project $project, array $data): mixed;

    /** @param Project $project @param array $data @return mixed */
    public function addBeforeAfter(Project $project, array $data): mixed;

    /** @param Project $project @param string $id @return bool */
    public function removeBeforeAfter(Project $project, string $id): bool;

    /** @param Project $project @return Project */
    public function feature(Project $project): Project;

    /** @param Project $project @return Project */
    public function unfeature(Project $project): Project;

    /** @param Project $project @return Project */
    public function duplicate(Project $project): Project;

    /** @param Project $project @param array $categoryIds @return Project */
    public function syncCategories(Project $project, array $categoryIds): Project;

    /** @param Project $project @param array $projectIds @return Project */
    public function attachRelated(Project $project, array $projectIds): Project;
}
