<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaxonomyTranslationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
