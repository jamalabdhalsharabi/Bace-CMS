<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPackRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:json,zip'],
            'locale' => ['required', 'string', 'max:10'],
        ];
    }
}
