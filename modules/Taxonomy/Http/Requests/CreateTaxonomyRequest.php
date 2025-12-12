<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaxonomyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'exists:taxonomy_types,slug'],
            'parent_id' => ['nullable', 'uuid', 'exists:taxonomies,id'],
            'featured_image_id' => ['nullable', 'uuid', 'exists:media,id'],
            'is_active' => ['nullable', 'boolean'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:60'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Taxonomy type is required.',
            'type.exists' => 'Invalid taxonomy type.',
            'translations.required' => 'At least one translation is required.',
            'translations.*.name.required' => 'Name is required for each translation.',
        ];
    }
}
