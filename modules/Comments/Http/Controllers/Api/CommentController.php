<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Http\Requests\CreateCommentRequest;
use Modules\Comments\Http\Requests\ReplyCommentRequest;
use Modules\Comments\Http\Requests\ReportCommentRequest;
use Modules\Comments\Http\Requests\VoteCommentRequest;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Comment CRUD Controller.
 *
 * Handles basic CRUD operations for comments including creation, updates,
 * deletion, voting, and reporting. Follows Single Responsibility Principle
 * by focusing solely on core comment operations.
 *
 * Other responsibilities are handled by specialized controllers:
 * - CommentListingController: Read-only listing and retrieval
 * - CommentModerationController: Moderation actions (approve, reject, etc.)
 * - CommentBulkController: Bulk operations on multiple comments
 * - CommentAdminController: Administrative operations (locking, banning)
 *
 * @package Modules\Comments\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
class CommentController extends BaseController
{
    public function __construct(
        protected CommentQueryService $queryService,
        protected CommentCommandService $commandService
    ) {
    }


    /** Create a new comment. */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        $comment = $this->commandService->create($request->validated());

        return $this->created(new CommentResource($comment), 'Comment submitted successfully');
    }

    /** Reply to an existing comment. */
    public function reply(ReplyCommentRequest $request, string $parentId): JsonResponse
    {
        $parent = $this->queryService->find($parentId);

        if (!$parent) {
            return $this->notFound('Parent comment not found');
        }

        $comment = $this->commandService->reply($parent, $request->validated());

        return $this->created(new CommentResource($comment), 'Reply submitted successfully');
    }

    /**
     * Soft delete a comment.
     *
     * @param string $id The comment UUID
     *
     * @return JsonResponse Success response or error
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);

            if (!$comment) {
                return $this->notFound('Comment not found');
            }

            $this->commandService->delete($comment);

            return $this->success(null, 'Comment deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete comment: ' . $e->getMessage());
        }
    }


    /**
     * Update a comment.
     *
     * @param Request $request The HTTP request
     * @param string $id The comment UUID
     *
     * @return JsonResponse Success response with updated comment or error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $comment = $this->commandService->update($comment, $request->all());
            
            return $this->success(new CommentResource($comment), 'Comment updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update comment: ' . $e->getMessage());
        }
    }


    /**
     * Report a comment.
     *
     * @param ReportCommentRequest $request The validated report request
     * @param string $id The comment UUID
     *
     * @return JsonResponse Success response or error
     */
    public function report(ReportCommentRequest $request, string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $validated = $request->validated();
            $this->commandService->report(
                $comment,
                $validated['reason'],
                $validated['details'] ?? null,
                Auth::id()
            );
            
            return $this->success(null, 'Comment reported successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to report comment: ' . $e->getMessage());
        }
    }


    /**
     * Vote on a comment (upvote or downvote).
     *
     * @param VoteCommentRequest $request The validated vote request
     * @param string $id The comment UUID
     *
     * @return JsonResponse Success response or error
     */
    public function vote(VoteCommentRequest $request, string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->find($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $validated = $request->validated();
            $this->commandService->vote($comment, $validated['vote'], Auth::id());
            
            return $this->success(null, 'Vote recorded successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to record vote: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a comment.
     *
     * @param string $id The comment UUID
     *
     * @return JsonResponse Success response or error
     */
    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $comment = $this->queryService->findWithTrashed($id);
            
            if (!$comment) {
                return $this->notFound('Comment not found');
            }
            
            $this->commandService->forceDelete($comment);
            
            return $this->success(null, 'Comment permanently deleted');
        } catch (\Exception $e) {
            return $this->error('Failed to permanently delete comment: ' . $e->getMessage());
        }
    }

}
