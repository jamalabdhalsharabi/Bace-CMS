<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Page;

/**
 * Interface PageServiceContract
 * 
 * Defines the contract for page management services.
 * Handles CRUD operations, workflow states, hierarchy, sections,
 * templates, locking, cloning, preview, and revisions.
 * 
 * @package Modules\Content\Contracts
 */
interface PageServiceContract
{
    /**
     * Get paginated list of pages with optional filters.
     *
     * @param array $filters Filter criteria (status, parent_id, search, etc.)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get hierarchical tree of all pages.
     *
     * @return Collection
     */
    public function getTree(): Collection;

    /**
     * Find a page by its ID.
     *
     * @param string $id The page UUID
     * @return Page|null
     */
    public function find(string $id): ?Page;

    /**
     * Find a page by its slug and optional locale.
     *
     * @param string $slug The page slug
     * @param string|null $locale Optional locale code
     * @return Page|null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Page;

    /**
     * Create a new page.
     *
     * @param array $data Page data including translations
     * @return Page
     */
    public function create(array $data): Page;

    /**
     * Update an existing page.
     *
     * @param Page $page The page to update
     * @param array $data Updated data
     * @return Page
     */
    public function update(Page $page, array $data): Page;

    /**
     * Soft delete a page.
     *
     * @param Page $page The page to delete
     * @return bool
     */
    public function delete(Page $page): bool;

    /**
     * Permanently delete a page.
     *
     * @param Page $page The page to force delete
     * @return bool
     */
    public function forceDelete(Page $page): bool;

    /**
     * Restore a soft-deleted page.
     *
     * @param string $id The page UUID
     * @return Page|null
     */
    public function restore(string $id): ?Page;

    /**
     * Save page as draft with updated data.
     *
     * @param Page $page The page
     * @param array $data Draft data
     * @return Page
     */
    public function saveDraft(Page $page, array $data): Page;

    /**
     * Submit page for editorial review.
     *
     * @param Page $page The page to submit
     * @return Page
     */
    public function submitForReview(Page $page): Page;

    /**
     * Approve a page after review.
     *
     * @param Page $page The page to approve
     * @param string|null $notes Optional approval notes
     * @return Page
     */
    public function approve(Page $page, ?string $notes = null): Page;

    /**
     * Reject a page after review.
     *
     * @param Page $page The page to reject
     * @param string|null $notes Optional rejection reason
     * @return Page
     */
    public function reject(Page $page, ?string $notes = null): Page;

    /**
     * Publish a page immediately.
     *
     * @param Page $page The page to publish
     * @return Page
     */
    public function publish(Page $page): Page;

    /**
     * Schedule a page for future publication.
     *
     * @param Page $page The page to schedule
     * @param \DateTime $date Publication date
     * @return Page
     */
    public function schedule(Page $page, \DateTime $date): Page;

    /**
     * Cancel scheduled publication.
     *
     * @param Page $page The scheduled page
     * @return Page
     */
    public function cancelSchedule(Page $page): Page;

    /**
     * Unpublish a published page.
     *
     * @param Page $page The page to unpublish
     * @return Page
     */
    public function unpublish(Page $page): Page;

    /**
     * Archive a page.
     *
     * @param Page $page The page to archive
     * @return Page
     */
    public function archive(Page $page): Page;

    /**
     * Restore a page from archive.
     *
     * @param Page $page The archived page
     * @return Page
     */
    public function unarchive(Page $page): Page;

    /**
     * Reorder pages in the hierarchy.
     *
     * @param array $order Array of page IDs in desired order
     * @return void
     */
    public function reorder(array $order): void;

    /**
     * Move a page to a new parent.
     *
     * @param Page $page The page to move
     * @param string|null $parentId New parent ID or null for root
     * @return Page
     */
    public function move(Page $page, ?string $parentId): Page;

    /**
     * Set a page as the homepage.
     *
     * @param Page $page The page to set as homepage
     * @return Page
     */
    public function setAsHomepage(Page $page): Page;

    /**
     * Set a page as the 404 error page.
     *
     * @param Page $page The page to set as 404
     * @return Page
     */
    public function setAs404(Page $page): Page;

    /**
     * Add a content section to a page.
     *
     * @param Page $page The page
     * @param array $sectionData Section configuration and content
     * @return Page
     */
    public function addSection(Page $page, array $sectionData): Page;

    /**
     * Update an existing section.
     *
     * @param Page $page The page
     * @param string $sectionId Section identifier
     * @param array $data Updated section data
     * @return Page
     */
    public function updateSection(Page $page, string $sectionId, array $data): Page;

    /**
     * Delete a section from a page.
     *
     * @param Page $page The page
     * @param string $sectionId Section identifier to delete
     * @return Page
     */
    public function deleteSection(Page $page, string $sectionId): Page;

    /**
     * Reorder sections within a page.
     *
     * @param Page $page The page
     * @param array $order Array of section IDs in desired order
     * @return Page
     */
    public function reorderSections(Page $page, array $order): Page;

    /**
     * Change the page template.
     *
     * @param Page $page The page
     * @param string $template Template identifier
     * @return Page
     */
    public function changeTemplate(Page $page, string $template): Page;

    /**
     * Lock a page for editing.
     *
     * @param Page $page The page to lock
     * @return Page
     */
    public function lock(Page $page): Page;

    /**
     * Unlock a locked page.
     *
     * @param Page $page The page to unlock
     * @return Page
     */
    public function unlock(Page $page): Page;

    /**
     * Duplicate a page with optional new slug.
     *
     * @param Page $page The page to duplicate
     * @param string|null $newSlug Optional custom slug for the copy
     * @return Page The duplicated page
     */
    public function duplicate(Page $page, ?string $newSlug = null): Page;

    /**
     * Generate preview data for a page.
     *
     * @param Page $page The page to preview
     * @return array Preview data
     */
    public function preview(Page $page): array;

    /**
     * Get all revisions for a page.
     *
     * @param Page $page The page
     * @return Collection Collection of revisions
     */
    public function getRevisions(Page $page): Collection;

    /**
     * Restore a page to a specific revision.
     *
     * @param Page $page The page
     * @param int $revisionNumber The revision number to restore
     * @return Page The restored page
     */
    public function restoreRevision(Page $page, int $revisionNumber): Page;
}
