<?php

declare(strict_types=1);

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShareOnSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platforms' => 'required|array',
            'platforms.*' => 'string|in:facebook,twitter,linkedin,pinterest',
        ];
    }
}
