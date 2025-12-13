<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Comments\Contracts\CommentServiceContract;
use Modules\Comments\Http\Requests\CreateCommentRequest;
use Modules\Comments\Http\Requests\ReplyCommentRequest;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Class CommentController
 * 
 * API controller for managing comments including CRUD operations,
 * moderation (approve, reject, spam), and replies.
 * 
 * @package Modules\Comments\Http\Controllers\Api
 */
class CommentController extends BaseController
{
    /**
     * The comment service instance for handling comment-related business logic.
     *
     * @var CommentServiceContract
     */
    protected CommentServiceContract $commentService;

    /**
     * Create a new CommentController instance.
     *
     * @param CommentServiceContract $commentService The comment service contract implementation
     */
    public function __construct(
        CommentServiceContract $commentService
    ) {
        $this->commentService = $commentService;
    }

    /** Get comments for a specific commentable model. */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|uuid',
        ]);

        $comments = $this->commentService->getForModel(
            $request->commentable_type,
            $request->commentable_id,
            $request->integer('per_page', 20)
        );

        return $this->paginated(CommentResource::collection($comments)->resource);
    }

    /** Get pending comments for moderation. */
    public function pending(Request $request): JsonResponse
    {
        $comments = $this->commentService->getPending($request->integer('per_page', 20));

        return $this->paginated(CommentResource::collection($comments)->resource);
    }

    /** Get a single comment by ID. */
    public function show(string $id): JsonResponse
    {
        $comment = $this->commentService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        return $this->success(new CommentResource($comment));
    }

    /** Create a new comment. */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        $comment = $this->commentService->create($request->validated());

        return $this->created(new CommentResource($comment), 'Comment submitted successfully');
    }

    /** Reply to an existing comment. */
    public function reply(ReplyCommentRequest $request, string $parentId): JsonResponse
    {
        $parent = $this->commentService->find($parentId);

        if (!$parent) {
            return $this->notFound('Parent comment not found');
        }

        $comment = $this->commentService->reply($parent, $request->validated());

        return $this->created(new CommentResource($comment), 'Reply submitted successfully');
    }

    /** Delete a comment. */
    public function destroy(string $id): JsonResponse
    {
        $comment = $this->commentService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $this->commentService->delete($comment);

        return $this->success(null, 'Comment deleted successfully');
    }

    /** Approve a pending comment. */
    public function approve(string $id): JsonResponse
    {
        $comment = $this->commentService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $comment = $this->commentService->approve($comment);

        return $this->success(new CommentResource($comment), 'Comment approved');
    }

    /** Reject a pending comment. */
    public function reject(string $id): JsonResponse
    {
        $comment = $this->commentService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $comment = $this->commentService->reject($comment);

        return $this->success(new CommentResource($comment), 'Comment rejected');
    }

    /** Mark a comment as spam. */
    public function spam(string $id): JsonResponse
    {
        $comment = $this->commentService->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found');
        }

        $comment = $this->commentService->markAsSpam($comment);

        return $this->success(new CommentResource($comment), 'Comment marked as spam');
    }
}
