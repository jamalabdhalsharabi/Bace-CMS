<?php

declare(strict_types=1);

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer'],
            'type' => ['required', 'string', 'in:adjustment,restock,sale,return'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
