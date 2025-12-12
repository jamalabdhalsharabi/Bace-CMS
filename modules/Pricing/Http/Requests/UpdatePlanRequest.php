<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'billing_periods' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
            'is_recommended' => ['nullable', 'boolean'],
            'translations' => ['nullable', 'array'],
            'translations.*.name' => ['required', 'string', 'max:100'],
            'translations.*.description' => ['nullable', 'string'],
        ];
    }
}
