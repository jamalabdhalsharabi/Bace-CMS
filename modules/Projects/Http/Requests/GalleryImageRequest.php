<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'media_id' => ['required', 'uuid', 'exists:media,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
