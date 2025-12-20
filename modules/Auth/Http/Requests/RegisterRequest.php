<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Registration Form Request.
 *
 * Validates user registration data including email uniqueness, password strength,
 * and optional profile information. Ensures data integrity before account creation.
 *
 * @package Modules\Auth\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Registration requests are always authorized as they are public endpoints
     * for creating new user accounts.
     *
     * @return bool Always returns true for registration attempts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines validation constraints for user registration:
     * - Email must be unique and valid format
     * - Password must meet security requirements and be confirmed
     * - Name fields are optional with length limits
     *
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Password>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
