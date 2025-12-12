<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'in:physical,digital,virtual,subscription'],
            'visibility' => ['nullable', 'string', 'in:visible,hidden,catalog_only,search_only'],
            'is_featured' => ['nullable', 'boolean'],
            'track_inventory' => ['nullable', 'boolean'],
            'allow_backorder' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'in:kg,g,lb,oz'],
            'tax_class' => ['nullable', 'string', 'max:50'],
            'dimensions' => ['nullable', 'array'],
            'translations' => ['nullable', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string', 'max:500'],
            'translations.*.description' => ['nullable', 'string'],
            'prices' => ['nullable', 'array'],
            'prices.*.currency_id' => ['required', 'uuid'],
            'prices.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
