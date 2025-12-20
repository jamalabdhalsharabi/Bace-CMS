<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Report Comment Request.
 *
 * Validates input data for reporting inappropriate or problematic comments.
 * Used when users flag comments for moderator review due to violations
 * of community guidelines or terms of service.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $reason The report reason category
 * @property string|null $details Optional detailed explanation
 */
class ReportCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as comment reporting is open to all users.
     * Specific authorization logic is handled in controllers.
     *
     * @return bool Always true to allow report attempts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the report reason and optional details.
     * 
     * Rules include:
     * - reason: Required string category (offensive, spam, inappropriate, etc.)
     * - details: Optional detailed explanation from reporter
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'in:offensive,spam,inappropriate,harassment,misinformation,other', 'max:50'],
            'details' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Custom validation messages
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please select a reason for reporting this comment.',
            'reason.in' => 'Invalid report reason selected.',
            'details.max' => 'Report details cannot exceed 1000 characters.',
        ];
    }
}
