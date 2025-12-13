<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTaxonomyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'data' => ['required', 'array'],
            'mode' => ['nullable', 'in:merge,replace'],
        ];
    }
}
