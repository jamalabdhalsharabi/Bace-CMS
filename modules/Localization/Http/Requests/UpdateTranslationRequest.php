<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'locale' => ['required', 'string', 'max:10'],
            'value' => ['required', 'string'],
        ];
    }
}
