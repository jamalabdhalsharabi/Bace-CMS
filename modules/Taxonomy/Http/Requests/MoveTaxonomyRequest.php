<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaxonomyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'uuid'],
            'position' => ['nullable', 'integer'],
        ];
    }
}
