<?php

declare(strict_types=1);

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'revision_id' => 'required|uuid',
        ];
    }
}
