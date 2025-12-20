<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Unban User Request.
 *
 * Validates input data for removing commenting bans from users.
 * Used by administrators to restore commenting privileges for users
 * who were previously banned but should now regain access.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $user_id The UUID of the user to unban
 */
class UnbanUserRequest extends FormRequest
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
     * Validates the user ID for unbanning operation.
     * 
     * Rules include:
     * - user_id: Required UUID of user to unban
     *
     * @return array<string, array<int, string>|string> Validation rules array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
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
        ];
    }
}
