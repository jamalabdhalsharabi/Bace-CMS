<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Contracts\PageServiceContract;
use Modules\Content\Http\Requests\ChangeTemplateRequest;
use Modules\Content\Http\Requests\CreatePageRequest;
use Modules\Content\Http\Requests\ReorderRequest;
use Modules\Content\Http\Requests\RestoreRevisionRequest;
use Modules\Content\Http\Requests\SchedulePageRequest;
use Modules\Content\Http\Requests\UpdatePageRequest;
use Modules\Content\Http\Resources\PageResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Class PageController
 * 
 * API controller for managing pages including CRUD, workflow,
 * hierarchy, sections, templates, locking, and revisions.
 * 
 * @package Modules\Content\Http\Controllers\Api
 */
class PageController extends BaseController
{
    /**
     * The page service instance for handling page-related business logic.
     *
     * @var PageServiceContract
     */
    protected PageServiceContract $pageService;

    /**
     * Create a new PageController instance.
     *
     * @param PageServiceContract $pageService The page service contract implementation
     */
    public function __construct(
        PageServiceContract $pageService
    ) {
        $this->pageService = $pageService;
    }

    /**
     * Display a paginated listing of pages.
     *
     * Supports filtering by status, parent_id, root pages, and search term.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of pages wrapped in PageResource
     */
    public function index(Request $request): JsonResponse
    {
        $pages = $this->pageService->list(
            filters: $request->only(['status', 'parent_id', 'root', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(PageResource::collection($pages)->resource);
    }

    /**
     * Get the hierarchical tree structure of all pages.
     *
     * Returns pages organized in a parent-child tree format.
     *
     * @return JsonResponse Collection of pages in tree structure
     */
    public function tree(): JsonResponse
    {
        $pages = $this->pageService->getTree();

        return $this->success(PageResource::collection($pages));
    }

    /**
     * Display the specified page by its UUID.
     *
     * @param string $id The UUID of the page to retrieve
     * @return JsonResponse The page data wrapped in PageResource or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        return $this->success(new PageResource($page));
    }

    /**
     * Display the specified page by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the page
     * @return JsonResponse The page data wrapped in PageResource or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $page = $this->pageService->findBySlug($slug);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        return $this->success(new PageResource($page));
    }

    /**
     * Store a newly created page in the database.
     *
     * @param CreatePageRequest $request The validated request containing page data
     * @return JsonResponse The newly created page wrapped in PageResource (HTTP 201)
     */
    public function store(CreatePageRequest $request): JsonResponse
    {
        $page = $this->pageService->create($request->validated());

        return $this->created(new PageResource($page), 'Page created successfully');
    }

    /**
     * Update the specified page in the database.
     *
     * @param UpdatePageRequest $request The validated request containing updated page data
     * @param string $id The UUID of the page to update
     * @return JsonResponse The updated page wrapped in PageResource or 404 error
     */
    public function update(UpdatePageRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $page = $this->pageService->update($page, $request->validated());

        return $this->success(new PageResource($page), 'Page updated successfully');
    }

    /**
     * Soft delete the specified page.
     *
     * The page will be moved to trash and can be restored later.
     *
     * @param string $id The UUID of the page to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $this->pageService->delete($page);

        return $this->success(null, 'Page deleted successfully');
    }

    /**
     * Publish the specified page, making it publicly visible.
     *
     * Changes the page status from draft/pending to published.
     *
     * @param string $id The UUID of the page to publish
     * @return JsonResponse The published page wrapped in PageResource or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $page = $this->pageService->publish($page);

        return $this->success(new PageResource($page), 'Page published successfully');
    }

    /**
     * Reorder pages based on the provided array of UUIDs.
     *
     * @param Request $request The request containing 'order' array of page UUIDs
     * @return JsonResponse Success message confirming reorder
     */
    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->pageService->reorder($request->validated()['order']);
        return $this->success(null, 'Pages reordered successfully');
    }

    /**
     * Set the specified page as the website homepage.
     *
     * @param string $id The UUID of the page to set as homepage
     * @return JsonResponse The updated page or 404 error
     */
    public function setHomepage(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->setAsHomepage($page)), 'Homepage set');
    }

    /**
     * Permanently delete the specified page from the database.
     *
     * This action cannot be undone. The page and all its data will be removed.
     *
     * @param string $id The UUID of the page to permanently delete
     * @return JsonResponse Success message or 404 error
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $page = \Modules\Content\Domain\Models\Page::withTrashed()->find($id);
        if (!$page) return $this->notFound('Page not found');
        $this->pageService->forceDelete($page);
        return $this->success(null, 'Page permanently deleted');
    }

    /**
     * Restore a soft-deleted page from the trash.
     *
     * @param string $id The UUID of the page to restore
     * @return JsonResponse The restored page or 404 error
     */
    public function restore(string $id): JsonResponse
    {
        $page = $this->pageService->restore($id);
        return $page ? $this->success(new PageResource($page)) : $this->notFound('Page not found');
    }

    /**
     * Save the current page content as a draft.
     *
     * Allows saving work-in-progress without publishing.
     *
     * @param Request $request The request containing draft data
     * @param string $id The UUID of the page
     * @return JsonResponse The updated page or 404 error
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->saveDraft($page, $request->all())));
    }

    /**
     * Submit the page for editorial review.
     *
     * Changes the page status to 'pending_review'.
     *
     * @param string $id The UUID of the page to submit
     * @return JsonResponse The updated page or 404 error
     */
    public function submitForReview(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->submitForReview($page)));
    }

    /**
     * Approve a page that is pending review.
     *
     * Changes the page status to 'approved' and optionally records reviewer notes.
     *
     * @param Request $request The request containing optional approval notes
     * @param string $id The UUID of the page to approve
     * @return JsonResponse The approved page or 404 error
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->approve($page, $request->notes)));
    }

    /**
     * Reject a page that is pending review.
     *
     * Changes the page status back to 'draft' and records rejection reason.
     *
     * @param Request $request The request containing rejection notes
     * @param string $id The UUID of the page to reject
     * @return JsonResponse The rejected page or 404 error
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->reject($page, $request->notes)));
    }

    /**
     * Schedule a page for future publication.
     *
     * The page will be automatically published at the specified date/time.
     *
     * @param Request $request The request containing 'scheduled_at' datetime
     * @param string $id The UUID of the page to schedule
     * @return JsonResponse The scheduled page or 404 error
     */
    public function schedule(SchedulePageRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->schedule($page, new \DateTime($request->validated()['scheduled_at']))));
    }

    /**
     * Cancel a scheduled publication for a page.
     *
     * Removes the scheduled_at date and returns the page to draft status.
     *
     * @param string $id The UUID of the page
     * @return JsonResponse The updated page or 404 error
     */
    public function cancelSchedule(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->cancelSchedule($page)));
    }

    /**
     * Unpublish a currently published page.
     *
     * Removes the page from public view and changes status to 'draft'.
     *
     * @param string $id The UUID of the page to unpublish
     * @return JsonResponse The unpublished page or 404 error
     */
    public function unpublish(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unpublish($page)));
    }

    /**
     * Archive the specified page.
     *
     * Moves the page to archived status for long-term storage.
     *
     * @param string $id The UUID of the page to archive
     * @return JsonResponse The archived page or 404 error
     */
    public function archive(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->archive($page)));
    }

    /**
     * Restore an archived page back to active status.
     *
     * @param string $id The UUID of the page to unarchive
     * @return JsonResponse The restored page or 404 error
     */
    public function unarchive(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unarchive($page)));
    }

    /**
     * Move a page to a different parent in the hierarchy.
     *
     * @param Request $request The request containing 'parent_id' (null for root)
     * @param string $id The UUID of the page to move
     * @return JsonResponse The moved page or 404 error
     */
    public function move(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->move($page, $request->parent_id)));
    }

    /**
     * Set the specified page as the custom 404 error page.
     *
     * @param string $id The UUID of the page to use as 404 page
     * @return JsonResponse The updated page or 404 error
     */
    public function set404(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->setAs404($page)));
    }

    /**
     * Add a new content section to the page.
     *
     * Sections are reusable content blocks within a page.
     *
     * @param Request $request The request containing section data
     * @param string $id The UUID of the page
     * @return JsonResponse The updated page or 404 error
     */
    public function addSection(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->addSection($page, $request->all())));
    }

    /**
     * Update an existing section within the page.
     *
     * @param Request $request The request containing updated section data
     * @param string $id The UUID of the page
     * @param string $sectionId The UUID of the section to update
     * @return JsonResponse The updated page or 404 error
     */
    public function updateSection(Request $request, string $id, string $sectionId): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->updateSection($page, $sectionId, $request->all())));
    }

    /**
     * Remove a section from the page.
     *
     * @param string $id The UUID of the page
     * @param string $sectionId The UUID of the section to delete
     * @return JsonResponse The updated page or 404 error
     */
    public function deleteSection(string $id, string $sectionId): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->deleteSection($page, $sectionId)));
    }

    /**
     * Reorder sections within the page.
     *
     * @param Request $request The request containing 'order' array of section IDs
     * @param string $id The UUID of the page
     * @return JsonResponse The updated page or 404 error
     */
    public function reorderSections(ReorderRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->reorderSections($page, $request->validated()['order'])));
    }

    /**
     * Change the template used by the page.
     *
     * @param Request $request The request containing 'template' name
     * @param string $id The UUID of the page
     * @return JsonResponse The updated page or 404 error
     */
    public function changeTemplate(ChangeTemplateRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->changeTemplate($page, $request->validated()['template'])));
    }

    /**
     * Lock the page for editing by the current user.
     *
     * Prevents other users from editing the page simultaneously.
     *
     * @param string $id The UUID of the page to lock
     * @return JsonResponse The locked page or 404 error
     */
    public function lock(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->lock($page)));
    }

    /**
     * Release the editing lock on the page.
     *
     * Allows other users to edit the page again.
     *
     * @param string $id The UUID of the page to unlock
     * @return JsonResponse The unlocked page or 404 error
     */
    public function unlock(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unlock($page)));
    }

    /**
     * Create a duplicate copy of the page.
     *
     * Clones the page with all its content and a new slug.
     *
     * @param Request $request The request containing optional 'new_slug'
     * @param string $id The UUID of the page to duplicate
     * @return JsonResponse The duplicated page (HTTP 201) or 404 error
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->created(new PageResource($this->pageService->duplicate($page, $request->new_slug)));
    }

    /**
     * Generate a preview of the page.
     *
     * Returns the page data as it would appear when rendered.
     *
     * @param string $id The UUID of the page to preview
     * @return JsonResponse The preview data or 404 error
     */
    public function preview(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success($this->pageService->preview($page));
    }

    /**
     * Get the revision history of the page.
     *
     * Returns a list of all saved revisions with timestamps and authors.
     *
     * @param string $id The UUID of the page
     * @return JsonResponse Array of revisions or 404 error
     */
    public function revisions(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success($this->pageService->getRevisions($page));
    }

    /**
     * Restore the page to a previous revision.
     *
     * Reverts the page content to the specified revision number.
     *
     * @param Request $request The request containing 'revision_number'
     * @param string $id The UUID of the page
     * @return JsonResponse The restored page or 404 error
     */
    public function restoreRevision(RestoreRevisionRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->restoreRevision($page, $request->validated()['revision_number'])));
    }
}
