<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeTaxonomyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'source_id' => ['required', 'uuid'],
            'target_id' => ['required', 'uuid', 'different:source_id'],
        ];
    }
}
