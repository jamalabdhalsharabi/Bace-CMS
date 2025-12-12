<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'in:contact,newsletter,survey,custom'],
            'success_message' => ['nullable', 'array'],
            'notification_emails' => ['nullable', 'array'],
            'notification_emails.*' => ['email'],
            'redirect_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'captcha_enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
            'fields' => ['nullable', 'array'],
            'fields.*.name' => ['required', 'string', 'max:50'],
            'fields.*.label' => ['required', 'array'],
            'fields.*.type' => ['required', 'string'],
            'fields.*.is_required' => ['nullable', 'boolean'],
        ];
    }
}
