<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMetricsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'metrics' => ['required', 'array'],
            'metrics.*.label' => ['required', 'string', 'max:100'],
            'metrics.*.value' => ['required', 'string', 'max:100'],
            'metrics.*.icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
