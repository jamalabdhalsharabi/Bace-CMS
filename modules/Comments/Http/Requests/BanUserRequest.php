<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ban User Request.
 *
 * Validates input data for banning users from commenting functionality.
 * Used by administrators to restrict commenting privileges for users who
 * violate community guidelines or terms of service.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $user_id The UUID of the user to ban
 * @property string|null $reason Optional reason for the ban
 * @property int|null $duration Optional ban duration in days
 */
class BanUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as authorization is handled by middleware.
     * Only administrators should have access to this endpoint.
     *
     * @return bool Always true to allow request processing
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates user ban parameters including target user ID,
     * optional reason, and optional duration.
     * 
     * Rules include:
     * - user_id: Required UUID of user to ban
     * - reason: Optional explanation for the ban (max 500 chars)
     * - duration: Optional ban duration in days (null = permanent)
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:365'],
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
            'user_id.required' => 'User ID is required.',
            'user_id.uuid' => 'Invalid user ID format.',
            'user_id.exists' => 'User not found.',
            'duration.min' => 'Ban duration must be at least 1 day.',
            'duration.max' => 'Ban duration cannot exceed 365 days.',
        ];
    }
}
