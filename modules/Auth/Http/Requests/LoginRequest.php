<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Login Form Request.
 *
 * Validates user login credentials including email format and password requirements.
 * Handles authentication request validation before processing login attempt.
 *
 * @package Modules\Auth\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Login requests are always authorized as authentication happens
     * after validation in the controller/service layer.
     *
     * @return bool Always returns true for login attempts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines validation constraints for login credentials:
     * - Email must be valid email format
     * - Password is required string
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
