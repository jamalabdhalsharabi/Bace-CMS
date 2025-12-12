<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'commentable_type' => ['required', 'string', 'max:100'],
            'commentable_id' => ['required', 'uuid'],
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ];

        if (!auth()->check()) {
            if (config('comments.guest_comments', true)) {
                $rules['author_name'] = ['required', 'string', 'max:100'];
                if (config('comments.require_email', true)) {
                    $rules['author_email'] = ['required', 'email', 'max:255'];
                }
            }
        }

        return $rules;
    }

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
