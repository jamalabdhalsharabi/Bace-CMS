<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Comments\Application\Services\CommentCommandService;
use Modules\Comments\Http\Requests\BulkCommentIdsRequest;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Comment Bulk Operations Controller.
 *
 * Handles bulk operations on multiple comments simultaneously
 * including bulk approval, rejection, deletion, and spam cleanup.
 * Follows Single Responsibility Principle by focusing solely on bulk operations.
 *
 * @package Modules\Comments\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
class CommentBulkController extends BaseController
{
    /**
     * Create a new CommentBulkController instance.
     *
     * @param CommentCommandService $commandService Service for executing comment commands
     */
    public function __construct(
        protected CommentCommandService $commandService
    ) {
    }

    /**
     * Bulk approve multiple comments.
     *
     * Approves all comments specified by their IDs in a single operation.
     * This is more efficient than individual approval requests for large batches.
     *
     * @param BulkCommentIdsRequest $request Validated request containing comment IDs
     * 
     * @return JsonResponse Success response with count of approved comments
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function bulkApprove(BulkCommentIdsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $count = $this->commandService->bulkApprove($validated['ids']);
            
            return $this->success([
                'approved' => $count,
                'message' => "Successfully approved {$count} comments"
            ], 'Comments approved');
        } catch (\Exception $e) {
            return $this->error('Failed to approve comments: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reject multiple comments.
     *
     * Rejects all comments specified by their IDs in a single operation.
     * Rejected comments are hidden from public view but remain in system.
     *
     * @param BulkCommentIdsRequest $request Validated request containing comment IDs
     * 
     * @return JsonResponse Success response with count of rejected comments
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function bulkReject(BulkCommentIdsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $count = $this->commandService->bulkReject($validated['ids']);
            
            return $this->success([
                'rejected' => $count,
                'message' => "Successfully rejected {$count} comments"
            ], 'Comments rejected');
        } catch (\Exception $e) {
            return $this->error('Failed to reject comments: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete multiple comments.
     *
     * Soft deletes all comments specified by their IDs in a single operation.
     * Deleted comments can be restored later if needed.
     *
     * @param BulkCommentIdsRequest $request Validated request containing comment IDs
     * 
     * @return JsonResponse Success response with count of deleted comments
     * @throws \Illuminate\Validation\ValidationException When request validation fails
     */
    public function bulkDelete(BulkCommentIdsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $count = $this->commandService->bulkDelete($validated['ids']);
            
            return $this->success([
                'deleted' => $count,
                'message' => "Successfully deleted {$count} comments"
            ], 'Comments deleted');
        } catch (\Exception $e) {
            return $this->error('Failed to delete comments: ' . $e->getMessage());
        }
    }

    /**
     * Clean spam comments.
     *
     * Permanently removes old spam comments from the system.
     * This helps maintain database performance and removes low-quality content.
     * Typically run as a scheduled maintenance task.
     *
     * @return JsonResponse Success response with count of cleaned spam comments
     * @throws \Exception When spam cleanup operation fails
     */
    public function cleanSpam(): JsonResponse
    {
        try {
            $count = $this->commandService->cleanSpam();
            
            return $this->success([
                'deleted' => $count,
                'message' => "Successfully cleaned {$count} spam comments"
            ], 'Spam comments cleaned');
        } catch (\Exception $e) {
            return $this->error('Failed to clean spam comments: ' . $e->getMessage());
        }
    }
}
