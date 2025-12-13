<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
