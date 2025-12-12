<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTaxonomyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'uuid', 'exists:taxonomies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'order.required' => 'Order array is required.',
            'order.*.uuid' => 'Each item must be a valid UUID.',
            'order.*.exists' => 'Invalid taxonomy ID.',
        ];
    }
}
