<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
