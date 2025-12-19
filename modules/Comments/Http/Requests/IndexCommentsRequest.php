<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|uuid',
        ];
    }
}
