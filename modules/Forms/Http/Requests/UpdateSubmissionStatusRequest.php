<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:new,read,spam,processed',
        ];
    }
}
