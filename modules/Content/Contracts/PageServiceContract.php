<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Page;

interface PageServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getTree(): Collection;
    public function find(string $id): ?Page;
    public function findBySlug(string $slug, ?string $locale = null): ?Page;
    public function create(array $data): Page;
    public function update(Page $page, array $data): Page;
    public function delete(Page $page): bool;
    public function forceDelete(Page $page): bool;
    public function restore(string $id): ?Page;

    // Workflow
    public function saveDraft(Page $page, array $data): Page;
    public function submitForReview(Page $page): Page;
    public function approve(Page $page, ?string $notes = null): Page;
    public function reject(Page $page, ?string $notes = null): Page;
    public function publish(Page $page): Page;
    public function schedule(Page $page, \DateTime $date): Page;
    public function cancelSchedule(Page $page): Page;
    public function unpublish(Page $page): Page;
    public function archive(Page $page): Page;
    public function unarchive(Page $page): Page;

    // Hierarchy
    public function reorder(array $order): void;
    public function move(Page $page, ?string $parentId): Page;
    public function setAsHomepage(Page $page): Page;
    public function setAs404(Page $page): Page;

    // Sections
    public function addSection(Page $page, array $sectionData): Page;
    public function updateSection(Page $page, string $sectionId, array $data): Page;
    public function deleteSection(Page $page, string $sectionId): Page;
    public function reorderSections(Page $page, array $order): Page;

    // Template
    public function changeTemplate(Page $page, string $template): Page;

    // Lock
    public function lock(Page $page): Page;
    public function unlock(Page $page): Page;

    // Clone
    public function duplicate(Page $page, ?string $newSlug = null): Page;

    // Preview
    public function preview(Page $page): array;

    // Revisions
    public function getRevisions(Page $page): Collection;
    public function restoreRevision(Page $page, int $revisionNumber): Page;
}
