<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'is_featured' => ['nullable', 'boolean'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_logo_id' => ['nullable', 'uuid'],
            'client_website' => ['nullable', 'url', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'metrics' => ['nullable', 'array'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
        ];
    }
}
