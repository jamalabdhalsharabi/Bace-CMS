<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetStockTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'track_stock' => 'required|boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ];
    }
}
