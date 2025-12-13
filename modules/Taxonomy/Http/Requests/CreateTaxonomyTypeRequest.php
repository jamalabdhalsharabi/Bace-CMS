<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaxonomyTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:taxonomy_types,name'],
            'slug' => ['required', 'string', 'max:100', 'unique:taxonomy_types,slug'],
            'hierarchical' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
