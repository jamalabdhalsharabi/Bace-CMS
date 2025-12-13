<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePricesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array'],
            'adjustment_type' => ['required', 'in:fixed,percentage'],
            'adjustment_value' => ['required', 'numeric'],
        ];
    }
}
