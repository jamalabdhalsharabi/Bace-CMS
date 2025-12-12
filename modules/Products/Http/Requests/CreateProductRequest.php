<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'in:physical,digital,virtual,subscription'],
            'status' => ['nullable', 'string', 'in:draft,pending,published,archived'],
            'visibility' => ['nullable', 'string', 'in:visible,hidden,catalog_only,search_only'],
            'is_featured' => ['nullable', 'boolean'],
            'track_inventory' => ['nullable', 'boolean'],
            'allow_backorder' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'in:kg,g,lb,oz'],
            'tax_class' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string', 'max:500'],
            'translations.*.description' => ['nullable', 'string'],
            'prices' => ['nullable', 'array'],
            'prices.*.currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'prices.*.amount' => ['required', 'numeric', 'min:0'],
            'prices.*.compare_at_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
