<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for scheduling block visibility.
 */
class ScheduleVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_at' => 'nullable|date',
            'hide_at' => 'nullable|date|after:show_at',
        ];
    }

    public function messages(): array
    {
        return [
            'hide_at.after' => 'Hide date must be after show date.',
        ];
    }
}
