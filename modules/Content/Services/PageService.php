<?php

declare(strict_types=1);

namespace Modules\Content\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Content\Contracts\PageServiceContract;
use Modules\Content\Domain\Models\Page;

/**
 * Class PageService
 *
 * Service class for managing pages including CRUD operations,
 * workflow, hierarchy, sections, templates, and revisions.
 *
 * @package Modules\Content\Services
 */
class PageService implements PageServiceContract
{
    /**
     * Retrieve a paginated list of pages with optional filtering.
     *
     * @param array $filters Optional filters: 'status', 'parent_id', 'root', 'search'
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated pages
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Page::with(['author', 'featuredImage', 'translation']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['root']) && $filters['root']) {
            $query->whereNull('parent_id');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
            );
        }

        return $query->ordered()->paginate($perPage);
    }

    /**
     * Get the full page hierarchy as a tree.
     *
     * @return Collection Root pages with nested children
     */
    public function getTree(): Collection
    {
        return Page::with(['children.children', 'translation'])
            ->whereNull('parent_id')
            ->ordered()
            ->get();
    }

    /**
     * Find a page by its UUID.
     *
     * @param string $id The page UUID
     *
     * @return Page|null The found page or null
     */
    public function find(string $id): ?Page
    {
        return Page::with(['author', 'featuredImage', 'translations', 'parent', 'children'])->find($id);
    }

    /**
     * Find a page by its URL slug.
     *
     * @param string $slug The URL slug
     * @param string|null $locale Optional locale
     *
     * @return Page|null The found page or null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Page
    {
        return Page::findBySlug($slug, $locale)?->load(['author', 'featuredImage', 'translations']);
    }

    /**
     * Create a new page with translations.
     *
     * @param array $data Page data including translations
     *
     * @return Page The created page
     *
     * @throws \Throwable If transaction fails
     */
    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data) {
            $page = Page::create([
                'parent_id' => $data['parent_id'] ?? null,
                'author_id' => $data['author_id'] ?? auth()->id(),
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'template' => $data['template'] ?? 'default',
                'status' => $data['status'] ?? 'draft',
                'is_homepage' => $data['is_homepage'] ?? false,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $page->translations()->create([
                        'locale' => $locale,
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                        'meta_keywords' => $trans['meta_keywords'] ?? null,
                    ]);
                }
            }

            if ($page->is_homepage) {
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            return $page->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Update an existing page and translations.
     *
     * @param Page $page The page to update
     * @param array $data Updated data
     *
     * @return Page The updated page
     *
     * @throws \Throwable If transaction fails
     */
    public function update(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data) {
            $page->update([
                'parent_id' => $data['parent_id'] ?? $page->parent_id,
                'featured_image_id' => $data['featured_image_id'] ?? $page->featured_image_id,
                'template' => $data['template'] ?? $page->template,
                'is_homepage' => $data['is_homepage'] ?? $page->is_homepage,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $page->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title' => $trans['title'],
                            'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                            'content' => $trans['content'] ?? null,
                            'meta_title' => $trans['meta_title'] ?? null,
                            'meta_description' => $trans['meta_description'] ?? null,
                            'meta_keywords' => $trans['meta_keywords'] ?? null,
                        ]
                    );
                }
            }

            if ($page->is_homepage) {
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            return $page->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Publish a page immediately.
     *
     * @param Page $page The page to publish
     *
     * @return Page The published page
     */
    public function publish(Page $page): Page
    {
        $page->update([
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
        ]);

        return $page->fresh();
    }

    /**
     * Soft-delete a page.
     *
     * @param Page $page The page to delete
     *
     * @return bool True if successful
     */
    public function delete(Page $page): bool
    {
        Page::where('parent_id', $page->id)->update(['parent_id' => $page->parent_id]);

        return $page->delete();
    }

    /**
     * Reorder pages by their IDs.
     *
     * @param array $order Array of page UUIDs in order
     *
     * @return void
     */
    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Page::where('id', $id)->update(['ordering' => $index + 1]);
        }
    }

    /**
     * Set a page as the homepage.
     *
     * @param Page $page The page to set as homepage
     *
     * @return Page The updated page
     */
    public function setAsHomepage(Page $page): Page
    {
        Page::where('is_homepage', true)->update(['is_homepage' => false]);
        $page->update(['is_homepage' => true]);

        return $page->fresh();
    }

    /**
     * Permanently delete a page.
     *
     * @param Page $page The page to delete
     *
     * @return bool True if successful
     */
    public function forceDelete(Page $page): bool
    {
        return $page->forceDelete();
    }

    /**
     * Restore a soft-deleted page.
     *
     * @param string $id The page UUID
     *
     * @return Page|null The restored page or null
     */
    public function restore(string $id): ?Page
    {
        $page = Page::withTrashed()->find($id);
        $page?->restore();
        return $page;
    }

    /**
     * Save page changes as a draft.
     *
     * @param Page $page The page to save
     * @param array $data The data to update
     *
     * @return Page The updated draft page
     */
    public function saveDraft(Page $page, array $data): Page
    {
        $data['status'] = 'draft';
        return $this->update($page, $data);
    }

    /**
     * Submit a page for review.
     *
     * @param Page $page The page to submit
     *
     * @return Page The submitted page
     */
    public function submitForReview(Page $page): Page
    {
        $page->update(['status' => 'pending_review', 'submitted_at' => now()]);
        return $page->fresh();
    }

    /**
     * Approve a page after review.
     *
     * @param Page $page The page to approve
     * @param string|null $notes Optional review notes
     *
     * @return Page The approved page
     */
    public function approve(Page $page, ?string $notes = null): Page
    {
        $page->update(['status' => 'approved', 'review_notes' => $notes]);
        return $page->fresh();
    }

    /**
     * Reject a page during review.
     *
     * @param Page $page The page to reject
     * @param string|null $notes Optional rejection reason
     *
     * @return Page The rejected page
     */
    public function reject(Page $page, ?string $notes = null): Page
    {
        $page->update(['status' => 'rejected', 'review_notes' => $notes]);
        return $page->fresh();
    }

    /**
     * Schedule a page for future publication.
     *
     * @param Page $page The page to schedule
     * @param \DateTime $date The publication date
     *
     * @return Page The scheduled page
     */
    public function schedule(Page $page, \DateTime $date): Page
    {
        $page->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $page->fresh();
    }

    /**
     * Cancel a scheduled page publication.
     *
     * @param Page $page The page to cancel
     *
     * @return Page The updated page
     */
    public function cancelSchedule(Page $page): Page
    {
        $page->update(['status' => 'draft', 'scheduled_at' => null]);
        return $page->fresh();
    }

    /**
     * Unpublish a published page.
     *
     * @param Page $page The page to unpublish
     *
     * @return Page The unpublished page
     */
    public function unpublish(Page $page): Page
    {
        $page->update(['status' => 'unpublished', 'published_at' => null]);
        return $page->fresh();
    }

    /**
     * Archive a page.
     *
     * @param Page $page The page to archive
     *
     * @return Page The archived page
     */
    public function archive(Page $page): Page
    {
        $page->update(['status' => 'archived', 'archived_at' => now()]);
        return $page->fresh();
    }

    /**
     * Restore an archived page to draft.
     *
     * @param Page $page The page to unarchive
     *
     * @return Page The unarchived page
     */
    public function unarchive(Page $page): Page
    {
        $page->update(['status' => 'draft', 'archived_at' => null]);
        return $page->fresh();
    }

    /**
     * Move a page to a new parent.
     *
     * @param Page $page The page to move
     * @param string|null $parentId New parent UUID or null
     *
     * @return Page The moved page
     */
    public function move(Page $page, ?string $parentId): Page
    {
        $page->update(['parent_id' => $parentId]);
        return $page->fresh();
    }

    /**
     * Set a page as the 404 error page.
     *
     * @param Page $page The page to set as 404
     *
     * @return Page The updated page
     */
    public function setAs404(Page $page): Page
    {
        Page::where('is_404', true)->update(['is_404' => false]);
        $page->update(['is_404' => true]);
        return $page->fresh();
    }

    /**
     * Add a section to a page.
     *
     * @param Page $page The page
     * @param array $sectionData Section data
     *
     * @return Page The updated page
     */
    public function addSection(Page $page, array $sectionData): Page
    {
        $page->sections()->create($sectionData);
        return $page->fresh(['sections']);
    }

    /**
     * Update a page section.
     *
     * @param Page $page The page
     * @param string $sectionId The section UUID
     * @param array $data Updated section data
     *
     * @return Page The updated page
     */
    public function updateSection(Page $page, string $sectionId, array $data): Page
    {
        $page->sections()->where('id', $sectionId)->update($data);
        return $page->fresh(['sections']);
    }

    /**
     * Delete a page section.
     *
     * @param Page $page The page
     * @param string $sectionId The section UUID
     *
     * @return Page The updated page
     */
    public function deleteSection(Page $page, string $sectionId): Page
    {
        $page->sections()->where('id', $sectionId)->delete();
        return $page->fresh(['sections']);
    }

    /**
     * Reorder page sections.
     *
     * @param Page $page The page
     * @param array $order Section UUIDs in order
     *
     * @return Page The updated page
     */
    public function reorderSections(Page $page, array $order): Page
    {
        foreach ($order as $index => $id) {
            $page->sections()->where('id', $id)->update(['sort_order' => $index]);
        }
        return $page->fresh(['sections']);
    }

    /**
     * Change the page template.
     *
     * @param Page $page The page
     * @param string $template Template name
     *
     * @return Page The updated page
     */
    public function changeTemplate(Page $page, string $template): Page
    {
        $page->update(['template' => $template]);
        return $page->fresh();
    }

    /**
     * Lock a page for editing.
     *
     * @param Page $page The page to lock
     *
     * @return Page The locked page
     */
    public function lock(Page $page): Page
    {
        $page->update(['locked_by' => auth()->id(), 'locked_at' => now()]);
        return $page->fresh();
    }

    /**
     * Unlock a locked page.
     *
     * @param Page $page The page to unlock
     *
     * @return Page The unlocked page
     */
    public function unlock(Page $page): Page
    {
        $page->update(['locked_by' => null, 'locked_at' => null]);
        return $page->fresh();
    }

    /**
     * Duplicate a page.
     *
     * @param Page $page The page to duplicate
     * @param string|null $newSlug Optional new slug
     *
     * @return Page The duplicated page
     */
    public function duplicate(Page $page, ?string $newSlug = null): Page
    {
        return DB::transaction(function () use ($page, $newSlug) {
            $clone = $page->replicate(['id', 'status', 'published_at', 'is_homepage']);
            $clone->status = 'draft';
            $clone->is_homepage = false;
            $clone->save();

            foreach ($page->translations as $trans) {
                $newTrans = $trans->replicate(['id', 'page_id']);
                $newTrans->page_id = $clone->id;
                $newTrans->slug = $newSlug ?? $trans->slug . '-copy';
                $newTrans->save();
            }

            return $clone->fresh(['translations']);
        });
    }

    /**
     * Get preview data for a page.
     *
     * @param Page $page The page to preview
     *
     * @return array Preview data
     */
    public function preview(Page $page): array
    {
        return [
            'id' => $page->id,
            'template' => $page->template,
            'content' => $page->translation?->content,
            'sections' => $page->sections,
            'url' => '/preview/page/' . $page->id,
        ];
    }

    /**
     * Get all revisions for a page.
     *
     * @param Page $page The page
     *
     * @return Collection Page revisions
     */
    public function getRevisions(Page $page): Collection
    {
        return $page->revisions()->get();
    }

    /**
     * Restore a page to a previous revision.
     *
     * @param Page $page The page
     * @param int $revisionNumber Revision number
     *
     * @return Page The restored page
     */
    public function restoreRevision(Page $page, int $revisionNumber): Page
    {
        $page->restoreRevision($revisionNumber);
        return $page->fresh();
    }
}
