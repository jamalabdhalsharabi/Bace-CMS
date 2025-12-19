<?php

declare(strict_types=1);

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating user avatar.
 */
class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'An avatar image is required.',
            'avatar.image' => 'The file must be an image.',
            'avatar.max' => 'The avatar must not exceed 2MB.',
        ];
    }
}
