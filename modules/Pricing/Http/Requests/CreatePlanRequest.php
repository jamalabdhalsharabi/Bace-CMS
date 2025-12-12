<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:50', 'unique:pricing_plans,slug'],
            'type' => ['nullable', 'string', 'in:subscription,one_time,usage_based'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'billing_periods' => ['required', 'array', 'min:1'],
            'billing_periods.*' => ['string', 'in:monthly,quarterly,yearly,lifetime'],
            'sort_order' => ['nullable', 'integer'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.name' => ['required', 'string', 'max:100'],
            'translations.*.description' => ['nullable', 'string'],
            'prices' => ['required', 'array'],
            'prices.*' => ['array'],
            'prices.*.*.currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'prices.*.*.amount' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*.key' => ['required', 'string', 'max:50'],
            'features.*.value' => ['required'],
            'features.*.type' => ['nullable', 'string', 'in:boolean,limit,text'],
            'limits' => ['nullable', 'array'],
        ];
    }
}
