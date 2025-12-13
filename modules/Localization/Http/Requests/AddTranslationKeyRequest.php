<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTranslationKeyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:100'],
            'translations' => ['required', 'array'],
        ];
    }
}
