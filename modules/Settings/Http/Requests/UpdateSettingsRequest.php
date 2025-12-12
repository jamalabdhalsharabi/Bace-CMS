<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],
            'settings.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required.',
            'settings.array' => 'Settings must be an array.',
        ];
    }
}
