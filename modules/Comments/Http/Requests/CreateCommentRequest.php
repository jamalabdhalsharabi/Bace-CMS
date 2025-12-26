<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Comment Request.
 *
 * Validates input data for creating new comments on commentable entities.
 * Handles both authenticated users and guest comments with appropriate
 * validation rules based on authentication status and system configuration.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $commentable_type The fully qualified class name of commentable entity
 * @property string $commentable_id The UUID of the commentable entity
 * @property string $content The comment text content (3-5000 characters)
 * @property string|null $author_name Guest author name (required for guest comments)
 * @property string|null $author_email Guest author email (required if email is configured as required)
 */
class CreateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as comment creation is open to all users
     * (authenticated and guests). Specific authorization logic is handled
     * in controllers and services based on system configuration.
     *
     * @return bool Always true to allow comment creation attempts
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
     * - commentable_type: Required string identifying the entity type
     * - commentable_id: Required UUID of the commentable entity  
     * - content: Required comment text (3-5000 characters)
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
            'commentable_type' => ['required', 'string', 'max:100'],
            'commentable_id' => ['required', 'uuid'],
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ];

        if (!request()->user()) {
            if (config('comments.guest_comments', true)) {
                $rules['author_name'] = ['required', 'string', 'max:100'];
                if (config('comments.require_email', true)) {
                    $rules['author_email'] = ['required', 'email', 'max:255'];
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom validation error messages.
     *
     * Provides user-friendly error messages for validation failures
     * that will be displayed to users when validation fails.
     *
     * @return array<string, string> Custom error messages keyed by rule
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.min' => 'Comment must be at least 3 characters.',
            'author_name.required' => 'Name is required for guest comments.',
            'author_email.required' => 'Email is required for guest comments.',
        ];
    }
}
