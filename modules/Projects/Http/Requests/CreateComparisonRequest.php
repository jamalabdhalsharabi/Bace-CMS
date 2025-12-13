<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateComparisonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'before_media_id' => ['required', 'uuid'],
            'after_media_id' => ['required', 'uuid'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
