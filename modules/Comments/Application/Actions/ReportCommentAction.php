<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Models\CommentReport;
use Modules\Core\Application\Actions\Action;

/**
 * Report Comment Action.
 *
 * Handles user reports on inappropriate or problematic comments.
 * Creates a report record in the comment_reports table and increments
 * the report counter on the comment for moderation tracking.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReportCommentAction extends Action
{
    /**
     * Execute the comment reporting action.
     *
     * Creates a new report record for the specified comment and increments
     * the comment's report_count field. When report_count exceeds a threshold,
     * the comment may be automatically hidden pending moderator review.
     *
     * The report includes:
     * - Reason category (offensive, spam, inappropriate, etc.)
     * - Optional detailed explanation from reporter
     * - Reporter's user ID (if authenticated) or IP address
     * - Timestamp for tracking and analysis
     *
     * @param Comment $comment The comment being reported
     * @param string $reason Report reason category
     * @param string|null $details Optional detailed explanation
     * @param string|null $userId Reporting user ID (null for guests)
     * 
     * @return CommentReport The created report record
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     * @throws \Exception When report creation fails
     */
    public function execute(
        Comment $comment,
        string $reason,
        ?string $details = null,
        ?string $userId = null
    ): CommentReport {
        return $this->transaction(function () use ($comment, $reason, $details, $userId) {
            // Create the report record
            $report = CommentReport::create([
                'comment_id' => $comment->id,
                'user_id' => $userId ?? $this->userId(),
                'reason' => $reason,
                'details' => $details,
                'status' => 'pending',
                'ip_address' => request()->ip(),
            ]);

            // Increment report counter on comment
            $comment->increment('report_count');

            // Auto-hide if report threshold exceeded
            $threshold = config('comments.auto_hide_threshold', 3);
            if ($comment->fresh()->report_count >= $threshold) {
                $comment->update(['status' => 'hidden']);
            }

            return $report;
        });
    }
}
