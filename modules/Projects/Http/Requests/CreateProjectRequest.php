<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'is_featured' => ['nullable', 'boolean'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_logo_id' => ['nullable', 'uuid', 'exists:media,id'],
            'client_website' => ['nullable', 'url', 'max:255'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'metrics' => ['nullable', 'array'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.excerpt' => ['nullable', 'string'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.challenge' => ['nullable', 'string'],
            'translations.*.solution' => ['nullable', 'string'],
            'translations.*.results' => ['nullable', 'string'],
        ];
    }
}
