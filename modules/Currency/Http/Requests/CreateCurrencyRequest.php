<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:3', 'unique:currencies,code'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'symbol_position' => ['nullable', 'in:before,after'],
            'decimal_separator' => ['nullable', 'string', 'size:1'],
            'thousands_separator' => ['nullable', 'string', 'size:1'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Currency code is required.',
            'code.size' => 'Currency code must be exactly 3 characters.',
            'code.unique' => 'This currency code already exists.',
            'name.required' => 'Currency name is required.',
            'symbol.required' => 'Currency symbol is required.',
        ];
    }
}
