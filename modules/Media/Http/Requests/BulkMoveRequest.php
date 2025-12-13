<?php

declare(strict_types=1);

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkMoveRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['uuid'],
            'folder_id' => ['nullable', 'uuid'],
        ];
    }
}
