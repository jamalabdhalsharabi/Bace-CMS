<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddVariantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'unique:product_variants,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'attributes' => ['required', 'array'],
        ];
    }
}
