<?php

declare(strict_types=1);

namespace Modules\Webhooks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string',
            'headers' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }
}
