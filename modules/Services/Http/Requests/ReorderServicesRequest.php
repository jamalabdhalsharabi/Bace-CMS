<?php

declare(strict_types=1);

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderServicesRequest extends FormRequest
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
