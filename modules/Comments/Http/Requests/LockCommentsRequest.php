<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LockCommentsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'uuid'],
        ];
    }
}
