<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetFallbackRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fallback_locale' => ['required', 'string', 'max:10'],
        ];
    }
}
