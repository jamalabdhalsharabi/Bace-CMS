<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetOnlineDetailsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'in:zoom,teams,meet,webex,custom'],
            'meeting_url' => ['required', 'url'],
            'meeting_id' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
        ];
    }
}
