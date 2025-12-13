<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormatAmountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric'],
            'currency_code' => ['required', 'string', 'max:3'],
        ];
    }
}
