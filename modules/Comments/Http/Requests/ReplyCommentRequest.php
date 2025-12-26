<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reply Comment Request.
 *
 * Validates input data for creating replies to existing comments.
 * Handles both authenticated users and guest replies with appropriate
 * validation rules based on authentication status and system configuration.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $content The reply text content (3-5000 characters)
 * @property string|null $author_name Guest author name (required for guest replies)
 * @property string|null $author_email Guest author email (required if email is configured as required)
 */
class ReplyCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as reply creation is open to all users
     * (authenticated and guests). Specific authorization logic is handled
     * in controllers and services based on system configuration.
     *
     * @return bool Always true to allow reply creation attempts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Returns dynamic validation rules based on user authentication status
     * and system configuration for guest comments and email requirements.
     * 
     * Basic rules always include:
     * - content: Required reply text (3-5000 characters)
     *
     * Additional rules for guest users (when not authenticated):
     * - author_name: Required if guest comments are enabled
     * - author_email: Required if email collection is configured
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ];

        if (!request()->user() && config('comments.guest_comments', true)) {
            $rules['author_name'] = ['required', 'string', 'max:100'];
            if (config('comments.require_email', true)) {
                $rules['author_email'] = ['required', 'email', 'max:255'];
            }
        }

        return $rules;
    }
}
