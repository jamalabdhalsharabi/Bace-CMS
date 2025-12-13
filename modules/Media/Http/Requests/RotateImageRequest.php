<?php

declare(strict_types=1);

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RotateImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'degrees' => ['required', 'integer', 'in:90,180,270,-90,-180,-270'],
        ];
    }
}
