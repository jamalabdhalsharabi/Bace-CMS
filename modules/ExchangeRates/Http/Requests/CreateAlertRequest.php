<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAlertRequest extends FormRequest
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
            'condition' => 'required|in:above,below,equals',
            'threshold' => 'required|numeric|min:0',
        ];
    }
}
