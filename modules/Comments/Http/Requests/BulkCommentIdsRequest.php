<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkCommentIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid',
        ];
    }
}
