<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|uuid',
            'updates.*.quantity' => 'required|integer',
        ];
    }
}
