<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetCapacityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'capacity' => ['required', 'integer', 'min:1'],
        ];
    }
}
