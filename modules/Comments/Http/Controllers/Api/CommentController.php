<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Application\Services\CommentQueryService;
use Modules\Comments\Http\Requests\BanUserRequest;
use Modules\Comments\Http\Requests\BulkCommentIdsRequest;
use Modules\Comments\Http\Requests\CreateCommentRequest;
use Modules\Comments\Http\Requests\IndexCommentsRequest;
use Modules\Comments\Http\Requests\LockCommentsRequest;
use Modules\Comments\Http\Requests\ReplyCommentRequest;
use Modules\Comments\Http\Requests\ReportCommentRequest;
use Modules\Comments\Http\Requests\UnbanUserRequest;
use Modules\Comments\Http\Requests\VoteCommentRequest;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Core\Http\Controllers\BaseController;

class CommentController extends BaseController
{
    public function __construct(
        protected CommentQueryService $queryService,
        protected CommentCommandService $commandService
    ) {
    }

    /** Get comments for a specific commentable model. */
    public function index(IndexCommentsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $comments = $this->queryService->getForModel(
            $data['commentable_type'],
            $data['commentable_id'],
            $request->integer('per_page', 20)
        );
        return $this->paginated(CommentResource::collection($comments)->resource);
    }

    /** Get pending comments for moderation. */
    public function pending(Request $request): JsonResponse
    {
        $comments = $this->queryService->getPending($request->integer('per_page', 20));

        return $this->paginated(CommentResource::collection($comments)->resource);
    }

    /** Get a single comment by ID. */
    public function show(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        return $this->success(new CommentResource($comment));
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

    /** Delete a comment. */
    public function destroy(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $this->commandService->delete($comment);

        return $this->success(null, 'Comment deleted successfully');
    }

    /** Approve a pending comment. */
    public function approve(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $comment = $this->commandService->approve($comment);

        return $this->success(new CommentResource($comment), 'Comment approved');
    }

    /** Reject a pending comment. */
    public function reject(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $comment = $this->commandService->reject($comment);

        return $this->success(new CommentResource($comment), 'Comment rejected');
    }

    /** Mark a comment as spam. */
    public function spam(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->markAsSpam($comment);
        return $this->success(new CommentResource($comment), 'Comment marked as spam');
    }

    /** Confirm comment is not spam. */
    public function notSpam(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->markAsNotSpam($comment);
        return $this->success(new CommentResource($comment), 'Comment marked as not spam');
    }

    /** Update a comment. */
    public function update(Request $request, string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->update($comment, $request->all());
        return $this->success(new CommentResource($comment));
    }

    /** Hide a comment. */
    public function hide(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->hide($comment);
        return $this->success(new CommentResource($comment), 'Comment hidden');
    }

    /** Show a hidden comment. */
    public function unhide(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->unhide($comment);
        return $this->success(new CommentResource($comment), 'Comment visible');
    }

    /** Report a comment. */
    public function report(ReportCommentRequest $request, string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $this->commandService->report($comment, $request->reason, Auth::id());
        return $this->success(null, 'Comment reported');
    }

    /** Pin a comment to top. */
    public function pin(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->pin($comment);
        return $this->success(new CommentResource($comment), 'Comment pinned');
    }

    /** Unpin a comment. */
    public function unpin(string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $comment = $this->commandService->unpin($comment);
        return $this->success(new CommentResource($comment), 'Comment unpinned');
    }

    /** Vote/react on a comment. */
    public function vote(VoteCommentRequest $request, string $id): JsonResponse
    {
        $comment = $this->queryService->find($id);
        if (!$comment) return $this->notFound('Comment not found');
        $this->commandService->vote($comment, $request->type, Auth::id());
        return $this->success(null, 'Vote recorded');
    }

    /** Force delete a comment permanently. */
    public function forceDestroy(string $id): JsonResponse
    {
        $comment = $this->queryService->findWithTrashed($id);
        if (!$comment) return $this->notFound('Comment not found');
        $this->commandService->forceDelete($comment);
        return $this->success(null, 'Comment permanently deleted');
    }

    /** Lock comments on content. */
    public function lockComments(LockCommentsRequest $request): JsonResponse
    {
        $this->commandService->lockComments($request->model_type, $request->model_id);
        return $this->success(null, 'Comments locked');
    }

    /** Unlock comments on content. */
    public function unlockComments(LockCommentsRequest $request): JsonResponse
    {
        $this->commandService->unlockComments($request->model_type, $request->model_id);
        return $this->success(null, 'Comments unlocked');
    }

    /** Ban a user from commenting. */
    public function banUser(BanUserRequest $request): JsonResponse
    {
        $this->commandService->banUser($request->user_id, $request->reason, $request->duration);
        return $this->success(null, 'User banned from commenting');
    }

    /** Unban a user. */
    public function unbanUser(UnbanUserRequest $request): JsonResponse
    {
        $this->commandService->unbanUser($request->validated()['user_id']);
        return $this->success(null, 'User unbanned');
    }

    /** Bulk approve comments. */
    public function bulkApprove(BulkCommentIdsRequest $request): JsonResponse
    {
        $count = $this->commandService->bulkApprove($request->validated()['ids']);
        return $this->success(['approved' => $count], 'Comments approved');
    }

    /** Bulk reject comments. */
    public function bulkReject(BulkCommentIdsRequest $request): JsonResponse
    {
        $count = $this->commandService->bulkReject($request->validated()['ids']);
        return $this->success(['rejected' => $count], 'Comments rejected');
    }

    /** Clean spam comments. */
    public function cleanSpam(): JsonResponse
    {
        $count = $this->commandService->cleanSpam();
        return $this->success(['deleted' => $count], 'Spam comments cleaned');
    }

    /** Get comment statistics. */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->queryService->getStats(
            $request->commentable_type,
            $request->commentable_id
        );
        return $this->success($stats);
    }
}
