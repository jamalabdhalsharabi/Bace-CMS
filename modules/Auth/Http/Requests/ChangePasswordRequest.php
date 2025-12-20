<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Change Password Form Request.
 *
 * Validates password change requests for authenticated users. Requires current password
 * verification and new password confirmation for security purposes.
 *
 * @package Modules\Auth\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Password change requests are authorized for authenticated users only.
     * Authorization is handled by route middleware (auth:sanctum).
     *
     * @return bool Always returns true as authorization is handled by middleware
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines validation constraints for password change:
     * - Current password is required for security verification
     * - New password must be at least 8 characters and confirmed
     *
     * @return array<string, string> Validation rules array
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * Provides user-friendly error messages for validation rule failures
     * to enhance user experience during password change process.
     *
     * @return array<string, string> Custom error messages array
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
