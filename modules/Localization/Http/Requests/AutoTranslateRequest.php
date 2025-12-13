<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoTranslateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'source_locale' => ['required', 'string', 'max:10'],
            'target_locale' => ['required', 'string', 'max:10'],
            'keys' => ['nullable', 'array'],
        ];
    }
}
