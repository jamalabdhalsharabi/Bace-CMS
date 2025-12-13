<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRecurringEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'base_event_id' => ['required', 'uuid'],
            'recurrence_pattern' => ['required', 'in:daily,weekly,monthly,yearly'],
            'occurrences' => ['required', 'integer', 'min:1', 'max:52'],
        ];
    }
}
