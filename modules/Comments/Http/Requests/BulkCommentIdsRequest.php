<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Bulk Comment IDs Request.
 *
 * Validates input data for bulk operations on multiple comments.
 * Used for operations like bulk approval, rejection, or deletion
 * where multiple comment IDs are processed simultaneously.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property array<int, string> $ids Array of comment UUIDs for bulk operations (minimum 1 required)
 */
class BulkCommentIdsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returns true to allow the request. Authorization for specific bulk
     * operations is handled at the controller/service level based on user
     * permissions and roles.
     *
     * @return bool Always true to allow bulk operation attempts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the array of comment IDs to ensure:
     * - At least one comment ID is provided
     * - All provided IDs are valid UUIDs
     * - The IDs array is properly formatted
     *
     * Rules include:
     * - ids: Required array with minimum 1 element
     * - ids.*: Each element must be a valid UUID format
     *
     * @return array<string, string> Validation rules array
     */
    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid',
        ];
    }
}
