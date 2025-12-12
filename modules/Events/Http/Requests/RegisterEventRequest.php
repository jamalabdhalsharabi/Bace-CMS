<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ticket_type_id' => ['nullable', 'uuid', 'exists:event_ticket_types,id'],
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'email', 'max:255'],
            'attendee_phone' => ['nullable', 'string', 'max:20'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
