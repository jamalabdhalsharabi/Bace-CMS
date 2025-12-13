<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetSalePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sale_price' => ['required', 'numeric', 'min:0'],
            'sale_start' => ['nullable', 'date'],
            'sale_end' => ['nullable', 'date', 'after:sale_start'],
        ];
    }
}
