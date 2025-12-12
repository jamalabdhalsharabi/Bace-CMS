<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ];

        if (!auth()->check() && config('comments.guest_comments', true)) {
            $rules['author_name'] = ['required', 'string', 'max:100'];
            if (config('comments.require_email', true)) {
                $rules['author_email'] = ['required', 'email', 'max:255'];
            }
        }

        return $rules;
    }
}
