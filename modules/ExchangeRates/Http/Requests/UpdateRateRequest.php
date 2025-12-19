<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_currency_id' => 'required|uuid|exists:currencies,id',
            'target_currency_id' => 'required|uuid|exists:currencies,id',
            'rate' => 'required|numeric|min:0.000001',
        ];
    }
}
