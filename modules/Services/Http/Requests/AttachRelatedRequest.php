<?php

declare(strict_types=1);

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachRelatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_ids' => 'required|array',
            'service_ids.*' => 'uuid',
        ];
    }
}
