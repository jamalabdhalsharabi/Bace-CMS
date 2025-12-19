<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|array',
        ];
    }
}
