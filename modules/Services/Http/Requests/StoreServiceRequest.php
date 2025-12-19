<?php

declare(strict_types=1);

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|max:100|unique:services,slug',
            'translations' => 'required|array|min:1',
            'translations.*.name' => 'required|string|max:200',
            'category_ids' => 'nullable|array',
        ];
    }
}
