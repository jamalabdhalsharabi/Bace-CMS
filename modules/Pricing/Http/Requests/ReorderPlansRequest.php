<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPlansRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*' => 'uuid',
        ];
    }
}
