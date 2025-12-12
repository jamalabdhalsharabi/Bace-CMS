<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'from' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'to' => ['required', 'string', 'size:3', 'exists:currencies,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'from.required' => 'Source currency is required.',
            'from.exists' => 'Invalid source currency.',
            'to.required' => 'Target currency is required.',
            'to.exists' => 'Invalid target currency.',
        ];
    }
}
