<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'event_type' => ['nullable', 'string', 'max:50'],
            'is_featured' => ['nullable', 'boolean'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string'],
            'is_online' => ['nullable', 'boolean'],
            'online_url' => ['nullable', 'url'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_free' => ['nullable', 'boolean'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
        ];
    }
}
