<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'uuid', 'exists:pricing_plans,id'],
            'billing_period' => ['required', 'string', 'in:monthly,quarterly,yearly,lifetime'],
            'payment_method' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
