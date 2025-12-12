<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'symbol_position' => ['nullable', 'in:before,after'],
            'decimal_separator' => ['nullable', 'string', 'size:1'],
            'thousand_separator' => ['nullable', 'string', 'size:1'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
