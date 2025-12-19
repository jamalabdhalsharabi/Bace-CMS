<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'from_currency_id' => 'required|uuid|exists:currencies,id',
            'to_currency_id' => 'required|uuid|exists:currencies,id',
        ];
    }
}
