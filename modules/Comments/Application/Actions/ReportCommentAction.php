<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

final class ReportCommentAction extends Action
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function execute(Comment $comment, ?string $reason = null): Comment
    {
        $meta = $comment->meta ?? [];
        $meta['report_reason'] = $reason;
        $meta['reported_at'] = now()->toISOString();

        $this->repository->update($comment->id, [
            'is_reported' => true,
            'meta' => $meta,
        ]);

        return $comment->fresh();
    }
}
