<?php

declare(strict_types=1);

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'uuid', 'exists:pages,id'],
            'template' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:draft,pending,published,archived'],
            'featured_image_id' => ['nullable', 'uuid', 'exists:media,id'],
            'is_homepage' => ['nullable', 'boolean'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.content' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:60'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:160'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }
}
