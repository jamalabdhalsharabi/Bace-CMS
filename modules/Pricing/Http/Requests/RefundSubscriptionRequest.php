<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:full,prorated,partial',
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
        ];
    }
}
