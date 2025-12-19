<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_path' => 'required|string|max:500|unique:redirects,source_path',
            'target_path' => 'required|string|max:500',
            'status_code' => 'nullable|integer|in:301,302,307,308',
            'is_regex' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
