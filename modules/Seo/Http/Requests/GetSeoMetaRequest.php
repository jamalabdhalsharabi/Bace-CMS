<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'id' => 'required|uuid',
            'locale' => 'nullable|string|size:2',
        ];
    }
}
