<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostponeEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'new_start_date' => ['required', 'date', 'after:now'],
            'new_end_date' => ['nullable', 'date', 'after:new_start_date'],
        ];
    }
}
