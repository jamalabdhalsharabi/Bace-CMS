<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSpeakerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo_id' => ['nullable', 'uuid'],
        ];
    }
}
