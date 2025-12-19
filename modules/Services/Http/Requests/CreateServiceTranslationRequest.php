<?php

declare(strict_types=1);

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'required|string|max:10',
            'name' => 'required|string|max:200',
        ];
    }
}
